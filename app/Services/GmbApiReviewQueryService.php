<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\Location;
use App\Review;
use App\ReviewReply;
use App\ReviewReplyTemplate;
use App\ReviewAggregate;
use App\LogApi;
use App\Services\GmbApiService;
use \Illuminate\Database\QueryException;

use Google_Client;
use Google_Service_MyBusiness_Location;
use Google_Service_MyBusiness_Profile;
use Google_Service_MyBusiness_LocalPost;
use Google_Service_MyBusiness_CallToAction;
use Google_Service_MyBusiness_LocalPostEvent;
use Google_Service_MyBusiness_Date;
use Google_Service_MyBusiness_TimeOfDay;
use Google_Service_MyBusiness_TimeInterval;
use Google_Service_MyBusiness_LocalPostOffer;
use Google_Service_MyBusiness_LocalPostProduct;
use Google_Service_MyBusiness_MediaItem;
use Google_Service_MyBusiness_LocationAssociation;
use Google_Service_Exception;
use Carbon\Carbon;

class GmbApiReviewQueryService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_review_count;
    private $_review_new_count;
    private $_review_reply_count;
    private $_started_at;
    private $_ended_at;

    public function __construct()
    {
        // for debug
      //  DB::enableQueryLog();
    }

    // クチコミを取得
    public function getReviews($gmbService, $gmbApiService, $gmbAccountId, $gmbLocationId)
    { 

        $this->_kubun = 1;
        $this->_class_function = "GmbApiReviewQueryService.getReviews";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_review_count = 0;
        $this->_review_new_count = 0;
        $this->_review_reply_count = 0;
        $this->_review_auto_reply_count = 0;

        if ($gmbAccountId == NULL && $gmbLocationId == NULL) {
            // 契約企業全てのブランド・店舗のクチコミを取得
            $accounts = Account::select(['gmb_account_id'])
                                ->active()
                                ->where('gmb_account_id', '<>', '102356320813998189642')
                                ->get();

            foreach ($accounts as $account) {
                $locations = Location::select(['gmb_location_id'])
                                    ->active()
                                    ->where('gmb_account_id', '=', $account['gmb_account_id'])
                                    ->get();

                foreach ($locations as $location) {
                    $this->_getLocationReviews($gmbService, $gmbApiService, $account['gmb_account_id'], $location['gmb_location_id']);
                    unset($location);
                }
                unset($locations);
            }
            unset($accounts);

        } else if ($gmbLocationId == NULL) {
            // 指定されたブランド配下の全店舗のクチコミを取得
            $locations = Location::select(['gmb_location_id'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)
                                ->get();

            foreach ($locations as $location) {
                $this->_getLocationReviews($gmbService, $gmbApiService, $gmbAccountId, $location['gmb_location_id']);
                unset($location);
            }
            unset($locations);

        } else {
            // 指定された店舗のクチコミを取得
            $this->_getLocationReviews($gmbService, $gmbApiService, $gmbAccountId, $gmbLocationId);
        }

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("review_count=%d, review_new_count=%d, review_reply_count=%d, review_auto_reply_count=%d", 
                                $this->_review_count, $this->_review_new_count, $this->_review_reply_count, $this->_review_auto_reply_count);
        $this->_logging($gmbApiService);

     //   logger()->error(DB::getQueryLog());
    }

    // 店舗のクチコミを取得
    private function _getLocationReviews($gmbService, $gmbApiService, $gmbAccountId, $gmbLocationId)
    { 

        try {

            $location = Location::select(['account_id', 'location_id', 'review_is_autoreplied', 'review_is_notified'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)
                                ->where('gmb_location_id', '=', $gmbLocationId)
                                ->first();

            if ($location != null) {
                $newReviewIds = array();
                $accountId = $location->account_id;
                $locationId = $location->location_id;
                $review_is_autoreplied = $location->review_is_autoreplied;
                $review_is_notified = $location->review_is_notified;

                $averageRating = 0.0;
                $totalReviewCount = 0;

                // 自動返信用のテンプレートを格納しておく
                $reviewReplyTemplateAry = array();
                if ($review_is_autoreplied != 0) {
                    $reviewReplyTemplates = ReviewReplyTemplate::select(['target_star_rating', 'template'])
                                                                ->active()
                                                                ->where('account_id', '=', $accountId)
                                                                ->orderBy('target_star_rating')
                                                                ->get();

                    foreach ($reviewReplyTemplates as $template) {
                        $key = "STAR_RATING_UNSPECIFIED";
                        if ($template['target_star_rating'] == 1) {
                            $key = "ONE";
                        } else if ($template['target_star_rating'] == 2) {
                            $key = "TWO";
                        } else if ($template['target_star_rating'] == 3) {
                            $key = "THREE";
                        } else if ($template['target_star_rating'] == 4) {
                            $key = "FOUR";
                        } else if ($template['target_star_rating'] == 5) {
                            $key = "FIVE";
                        }

                        if (empty($reviewReplyTemplateAry[$key])) {
                            $reviewReplyTemplateAry[$key] = $template['template'];
                        } else {
                            if (rand(0, 1) == 1) {
                                $reviewReplyTemplateAry[$key] = $template['template'];
                            }
                        }

                        unset($template);
                    }
                }

                // 取込み済のクチコミより過去のクチコミは取り込まない
                $review = Review::select(DB::raw('DATE_SUB(MAX(gmb_update_time),INTERVAL 1 DAY) AS max_gmb_update_time'))
                                    ->active()
                                    ->where('gmb_account_id', '=', $gmbAccountId)
                                    ->where('gmb_location_id', '=', $gmbLocationId)
                                    ->first();
                $max_gmb_update_time = strtotime($review->max_gmb_update_time);

                $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId;

                $optParams = array('pageSize' => 200);
                $gmb = $gmbService->accounts_locations_reviews->listAccountsLocationsReviews($name, $optParams);
                if ($gmb) {

                    // ダッシュボード集計値
                    $averageRating = $gmb['averageRating'];
                    $totalReviewCount = $gmb['totalReviewCount'];
                    if (! is_numeric($averageRating))       $averageRating = 0.0;
                    if (! is_numeric($totalReviewCount))    $totalReviewCount = 0;

                    foreach($gmb['reviews'] as $review){
                        // 取り込み済？
                        $gmb_updateTime = strtotime($gmbApiService->covertTimezone2Jst($review['updateTime']));
                        if ($max_gmb_update_time >= $gmb_updateTime){
                            $gmb['nextPageToken'] = null;
                            unset($review);
                            break;
                        }

                        $nameAry = explode("/", $review['name']);

                        $columns = array();
                        $columns['location_id'] = $locationId;
                        $columns['gmb_account_id'] = $nameAry[1];
                        $columns['gmb_location_id'] = $nameAry[3];
                        $columns['gmb_review_id'] = str_replace('%3D%3D', '', $nameAry[5]);
                        $columns['review_is_autoreplied'] = $review_is_autoreplied;
                        $columns['review_is_notified'] = $review_is_notified;

                        $newId = $this->_syncReview($gmbService, $gmbApiService, $columns, $reviewReplyTemplateAry, $review);
                        if ($newId > 0) {
                            $newReviewIds[] = $newId;
                            $this->_review_new_count++;
                        }

                        $this->_review_count++;
                        unset($nameAry);
                        unset($columns);
                        unset($review);
                    }
        
                    do{
                        if (isset($gmb['nextPageToken'])) {
                            $optParams = array('pageSize' => 200, 'pageToken' => $gmb['nextPageToken']);
                            $gmb = $gmbService->accounts_locations_reviews->listAccountsLocationsReviews($name, $optParams);
                            if ($gmb)  {
                                foreach($gmb['reviews'] as $review){
                                    $nameAry = explode("/", $review['name']);

                                    $columns = array();
                                    $columns['location_id'] = $locationId;
                                    $columns['gmb_account_id'] = $nameAry[1];
                                    $columns['gmb_location_id'] = $nameAry[3];
                                    $columns['gmb_review_id'] = str_replace('%3D%3D', '', $nameAry[5]);
                                    $columns['review_is_autoreplied'] = $review_is_autoreplied;
                                    $columns['review_is_notified'] = $review_is_notified;

                                    $newId = $this->_syncReview($gmbService, $gmbApiService, $columns, $reviewReplyTemplateAry, $review);
                                    if ($newId > 0) {
                                        $newReviewIds[] = $newId;
                                        $this->_review_new_count++;
                                    }

                                    $this->_review_count++;
                                    unset($nameAry);
                                    unset($columns);
                                    unset($review);
                                }
                              //  unset($gmb);

                            } else {
                                unset($gmb);
                                break;
                            }
                        }

                    } while(isset($gmb['nextPageToken']));
                }
                unset($gmb);

                // ダッシュボード集計値を更新
                $this->_saveReviewAggregate($gmbService, $gmbApiService, $locationId, $gmbAccountId, $gmbLocationId, $averageRating, $totalReviewCount);

                // 新しいクチコミあり
                if (count($newReviewIds) > 0) {
                    // 通知する設定の場合
                    if ($review_is_notified) {
                        // 新しいクチコミを対象者にメール通知する
                        $this->_notifyNewReviews($accountId, $locationId, $newReviewIds);
                    }
                }
                // 開放
                unset($newReviewIds);
            }

        }catch(Google_Service_Exception $e){
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiReviewQueryService._getLocationReviews";
            $this->_detail = sprintf("name=%s", 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            // 開放
            $location = null;
            $review = null;
        }

    }

    // クチコミを同期
    private function _syncReview($gmbService, $gmbApiService, $columns, $reviewReplyTemplateAry, $gmb)
    { 
        $reviewId = 0;
        $newReviewId = 0;
        $review = Review::active()
                        ->where('gmb_account_id', '=', $columns['gmb_account_id'])
                        ->where('gmb_location_id', '=', $columns['gmb_location_id'])
                        ->where('gmb_review_id', '=', $columns['gmb_review_id'])
                        ->first();

        if ($review == null) {
            // 新しいクチコミを取得
            $review = new Review;
            $newReviewId = $this->_saveReview($gmbService, $gmbApiService, $review, $columns['location_id'], $gmb);
            $reviewId = $newReviewId;
        } else {
            $reviewId = $review->review_id;
            $this->_saveReview($gmbService, $gmbApiService, $review, $columns['location_id'], $gmb);
        }

        // クチコミ返信を同期
        if ($reviewId != 0 && (isset($gmb["reviewReply"]) || $columns['review_is_autoreplied'] != 0)) {
            $columns['review_id'] = $reviewId;
            $this->_syncReviewReply($gmbService, $gmbApiService, $columns, $reviewReplyTemplateAry, $gmb);
        }

        return $newReviewId;
    }

    // ダッシュボード集計値を更新
    private function _saveReviewAggregate($gmbService, $gmbApiService, $locationId, $gmbAccountId, $gmbLocationId, $averageRating, $totalReviewCount)
    {

        try {

            // 未返信数を取得 -> reviewsを集計するためコメントアウト
            /*
            $review_count = Review::where('location_id', '=', $locationId)
                            ->where(function($query){
                                $query->whereNull('gmb_review_reply_comment')->orWhere('gmb_review_reply_comment', '=', '');
                            })
                            ->get()
                            ->count();

            $review_unreplied_count = $review_count;
            if ($review_unreplied_count > $totalReviewCount) $review_unreplied_count = $totalReviewCount;
            */
            $review_unreplied_count = 0;


            $reviewAggregate = ReviewAggregate::where('location_id', '=', $locationId)
                            ->first();

            if ($reviewAggregate  == null) {
                $reviewAggregate = new ReviewAggregate;

                $reviewAggregate->location_id  = $locationId;
                $reviewAggregate->gmb_account_id  = $gmbAccountId;
                $reviewAggregate->gmb_location_id  = $gmbLocationId;

                $reviewAggregate->gmb_average_rating  = $averageRating;
                $reviewAggregate->gmb_total_review_count  = $totalReviewCount;
                $reviewAggregate->review_unreplied_count  = $review_unreplied_count;
                $reviewAggregate->create_user_id  = 0;
                $reviewAggregate->save();

            } else {
                $reviewAggregate->gmb_average_rating  = $averageRating;
                $reviewAggregate->gmb_total_review_count  = $totalReviewCount;
                $reviewAggregate->review_unreplied_count  = $review_unreplied_count;
                $reviewAggregate->update_user_id  = 0;
                $reviewAggregate->save();
            }

        } catch ( QueryException $e ) {
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiReviewQueryService._saveReviewAggregate";
            $this->_detail = sprintf("name=accounts/%s/locations/%s", $gmbAccountId, $gmbLocationId);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $reviewAggregate = null;
            $review_count = null;
        }
    }

    // クチコミを保存
    private function _saveReview($gmbService, $gmbApiService, $review, $locationId, $gmb)
    {
        $reviewId = 0;

        try {

            $review->location_id  = $locationId;

            $nameAry = explode("/", $gmb['name']);
            $review->gmb_account_id  = $nameAry[1];
            $review->gmb_location_id  = $nameAry[3];
            $review->gmb_review_id  = $nameAry[5];
            unset($nameAry);
    
            $review->gmb_reviewer_profile_photo_url  = $gmb["reviewer"]["profilePhotoUrl"];
            $review->gmb_reviewer_display_name  = $gmb["reviewer"]["displayName"];
            $review->gmb_reviewer_is_anonymous  = $gmbApiService->checkGmbJson($gmb["reviewer"]["isAnonymous"], 0);
            $review->gmb_star_rating  = $gmb["starRating"];
            $review->gmb_comment = $gmbApiService->removeTranslatedGoogleString($gmb["comment"]);
            $review->gmb_create_time  = $gmbApiService->covertTimezone2Jst($gmb["createTime"]);
            $review->gmb_update_time  = $gmbApiService->covertTimezone2Jst($gmb["updateTime"]);

            if (isset($gmb["reviewReply"])) {
                $review->gmb_review_reply_comment = $gmbApiService->removeTranslatedGoogleString($gmb["reviewReply"]["comment"]);
                $review->gmb_review_reply_update_time  = $gmbApiService->covertTimezone2Jst($gmb["reviewReply"]["updateTime"]);
            }
            
            $review->is_deleted  = 0;
            $review->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $review->sync_time  = Carbon::now();
            $review->create_user_id  = 0;
            
            $review->save();
            $reviewId = $review->review_id;
          
        } catch ( QueryException $e ) {
            $reviewId = 0;
           
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiReviewQueryService._saveReview";
            $this->_detail = sprintf("name=%s", $gmb['name']);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $review = null;
        }

        return $reviewId;
    }

    // クチコミ返信を同期
    private function _syncReviewReply($gmbService, $gmbApiService, $columns, $reviewReplyTemplateAry, $gmb)
    {
        $reviewReply = ReviewReply::active()
                                ->where('review_id', '=', $columns['review_id'])
                                ->first();

        $is_auto = false;
        if ($reviewReply == null) {
            if (isset($gmb["reviewReply"])) {
                $reviewReply = new ReviewReply;
                $this->_saveReviewReply($gmbService, $gmbApiService, $reviewReply, $is_auto, $columns, $reviewReplyTemplateAry, $gmb);
                $this->_review_reply_count++;

            } else {
                if ($columns['review_is_autoreplied'] == 1) {
                    // すべてに自動投稿
                    $is_auto = true;
                    $reviewReply = new ReviewReply;
                    $this->_saveReviewReply($gmbService, $gmbApiService, $reviewReply, $is_auto, $columns, $reviewReplyTemplateAry, $gmb);
                } else if ($columns['review_is_autoreplied'] == 2) {
                    // コメントなしのみ自動投稿
                    $gmb_comment = $gmbApiService->removeTranslatedGoogleString($gmb["comment"]);
                    if ($gmb_comment == "" && strlen($gmb_comment) == 0) {
                        $is_auto = true;
                        $reviewReply = new ReviewReply;
                        $this->_saveReviewReply($gmbService, $gmbApiService, $reviewReply, $is_auto, $columns, $reviewReplyTemplateAry, $gmb);
                    }
                }
            }

        } else {
            if ($reviewReply->sync_status == config('const.SYNC_STATUS.SYNCED')) {
              $this->_saveReviewReply($gmbService, $gmbApiService, $reviewReply, $is_auto, $columns, $reviewReplyTemplateAry, $gmb);
            }
        }
    }
    
    // クチコミ返信を保存
    private function _saveReviewReply($gmbService, $gmbApiService, $reviewReply, $is_auto, $columns, $reviewReplyTemplateAry, $gmb)
    {

        try {

            DB::beginTransaction();

            if ($is_auto) {
                // 自動投稿
                if (count($reviewReplyTemplateAry) > 0) {
                    // 評価点に対応するテンプレートがある場合は、クチコミ返信レコードを生成する
                    $starRating = $gmb['starRating'];
                    if ( array_key_exists($starRating, $reviewReplyTemplateAry) ) {
                        // 返信
                        $reviewReply->review_id  = $columns['review_id'];
                        $reviewReply->gmb_comment  = $reviewReplyTemplateAry[$starRating];
                        $reviewReply->is_deleted  = 0;
                        $reviewReply->sync_type  = config('const.SYNC_TYPE.CREATE');
                        $reviewReply->sync_status  = config('const.SYNC_STATUS.QUEUED');
                        $reviewReply->scheduled_sync_time  = Carbon::now();
                        $reviewReply->create_user_id = 0;
                        $reviewReply->create_time  = Carbon::now();

                        $reviewReply->save();

                        // クチコミ
                        $review = Review::select('review_id')
                                        ->where('review_id', '=', $columns['review_id'])
                                        ->first();

                        if ($review != null) {
                            $review->sync_type  = config('const.SYNC_TYPE.CREATE');
                            $review->sync_status  = config('const.SYNC_STATUS.QUEUED');
                            $review->scheduled_sync_time  = Carbon::now();
                            $review->save();
                        }

                        $this->_review_auto_reply_count++;
                    }
                }

            } else {
                $comment = $gmbApiService->removeTranslatedGoogleString($gmb["reviewReply"]["comment"]);
                if ($comment != "") {
                    // 返信
                    $reviewReply->review_id  = $columns['review_id'];
                    $reviewReply->gmb_comment  = $comment;
                    $reviewReply->gmb_update_time  = $gmbApiService->covertTimezone2Jst($gmb["reviewReply"]["updateTime"]);
                    $reviewReply->is_deleted  = 0;
                    $reviewReply->sync_status  = config('const.SYNC_STATUS.SYNCED');
                    $reviewReply->sync_time  = Carbon::now();
            
                    $reviewReply->save();

                    // クチコミ
                    $review = Review::select('review_id')
                                    ->where('review_id', '=', $columns['review_id'])
                                    ->first();

                    if ($review != null) {
                        $review->sync_status  = config('const.SYNC_STATUS.SYNCED');
                        $review->scheduled_sync_time  = null;
                        $review->sync_time  = Carbon::now();
                        $review->save();
                    }
                }
            }

            DB::commit();

        } catch ( QueryException $e ) {
            DB::rollBack();

            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiReviewQueryService._saveReviewReply";
            $this->_detail = sprintf("review_id=%d", $columns['review_id']);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $reviewReply = null;
        }
    }

    // 新しいクチコミがあることを対象者にメール通知する SES利用
    private function _notifyNewReviews($accountId, $locationId, $newReviewIds) {


    }

    // ログ出力
    private function _logging($gmbApiService) {
        $this->_ended_at = Carbon::now();
        $gmbApiService->logApiBatch($this->_kubun, 
                                    $this->_proc_exit,
                                    $this->_class_function,
                                    $this->_detail,
                                    $this->_exception,
                                    $this->_started_at,
                                    $this->_ended_at);
    }
    
    private function _debug($msg) {
        var_dump($msg);
    }

  }