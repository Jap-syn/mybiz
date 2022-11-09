<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\Location;
use App\Review;
use App\ReviewReply;
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
use Google_Service_MyBusiness_Review;
use Google_Service_MyBusiness_ReviewReply;
use Google_Service_Exception;
use Carbon\Carbon;

class GmbApiReviewRegistService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_review_count;
    private $_review_reply_count;
    private $_started_at;
    private $_ended_at;

    public function __construct()
    {
        // for debug
     //   DB::enableQueryLog();
    }

    // クチコミ返信を同期（新規作成、変更、削除）
    public function registReviewReplies($gmbService, $gmbApiService)
    { 

        $this->_kubun = 0;
        $this->_class_function = "GmbApiReviewRegistService.registReviewReplies";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_review_reply_create_count = 0;
        $this->_review_reply_patch_count = 0;
        $this->_review_reply_delete_count = 0;
        $this->_review_reply_failed_count = 0;

        $reviewReplies = ReviewReply::select(['reviews.review_id'
                                            , 'reviews.gmb_account_id'
                                            , 'reviews.gmb_location_id'
                                            , 'reviews.gmb_review_id'
                                            , 'review_replies.review_reply_id'
                                            , 'review_replies.sync_type'
                                            , 'review_replies.sync_status'
                                            , 'review_replies.gmb_comment'])
                                ->join('reviews', function ($join) {
                                    $join->on('reviews.review_id', '=', 'review_replies.review_id')
                                        ->where('reviews.is_deleted', '=', config('const.IS_DELETED.OFF'));
                                })
                                ->queued()
                                ->where('review_replies.scheduled_sync_time', '<=', Carbon::now())
                                ->orderBy('reviews.gmb_account_id', 'asc')
                                ->orderBy('reviews.gmb_location_id', 'asc')
                                ->get();

        foreach($reviewReplies as $rs){

            try {

                $name = sprintf('accounts/%s/locations/%s/reviews/%s', $rs["gmb_account_id"], $rs["gmb_location_id"], $rs["gmb_review_id"]);

                if ($rs["sync_type"] == config('const.SYNC_TYPE.CREATE') || $rs["sync_type"] == config('const.SYNC_TYPE.PATCH')) {
                    // 新規返信、変更
                    $postBody = new Google_Service_MyBusiness_ReviewReply();
                    $postBody->setComment($rs["gmb_comment"]);
                    $gmbService->accounts_locations_reviews->updateReply($name, $postBody);

                    // 同期成功
                    $this->_saveReviewReply($gmbService, $gmbApiService, config('const.SYNC_STATUS.SYNCED'), $rs);

                    // ログ集計
                    if ($rs["sync_type"] == config('const.SYNC_TYPE.CREATE')) {
                        $this->_review_reply_create_count++;
                    } else if ($rs["sync_type"] == config('const.SYNC_TYPE.PATCH')) {
                        $this->_review_reply_patch_count++;
                    }

                    // 連続返信回避
                    sleep(15);

                } else if ($rs["sync_type"] == config('const.SYNC_TYPE.DELETE')) {
                    // 削除
                    $gmbService->accounts_locations_reviews->deleteReply($name);

                    // 同期成功
                    $this->_saveReviewReply($gmbService, $gmbApiService, config('const.SYNC_STATUS.SYNCED'), $rs);
                    $this->_review_reply_delete_count++;
                }

            }catch(Google_Service_Exception $e){
                // 同期失敗
                $sync_status = config('const.SYNC_STATUS.FAILED');
                if ($rs["sync_status"] == config('const.SYNC_STATUS.FAILED')) {
                    $sync_status = config('const.SYNC_STATUS.CANCEL');
                }

                $this->_saveReviewReply($gmbService, $gmbApiService, $sync_status, $rs);
                $this->_review_reply_failed_count++;

                // エラーログ
                $this->_proc_exit = -1;
                $this->_detail = sprintf('accounts/%s/locations/%s/reviews/%s', $rs["gmb_account_id"], $rs["gmb_location_id"], $rs["gmb_review_id"]);
                $this->_exception = $e->getMessage();
                $this->_logging($gmbApiService);
            }

            // 開放
            unset($rs);
        }

        $reviewReplies = null;

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("create=%d, patch=%d, delete=%d, failed=%d"
                                ,$this->_review_reply_create_count
                                ,$this->_review_reply_patch_count
                                ,$this->_review_reply_delete_count
                                ,$this->_review_reply_failed_count);
        $this->_logging($gmbApiService);

     //   logger()->error(DB::getQueryLog());
    }

    // 同期結果を更新
    private function _saveReviewReply($gmbService, $gmbApiService, $sync_status, $rs)
    { 

        try {

            $reviewReply = ReviewReply::where('review_reply_id', '=', $rs["review_reply_id"])
                                        ->first();

            if ($reviewReply != null) {

                DB::beginTransaction();

                $review_id = $reviewReply->review_id;
            
                $reviewReply->sync_status  = $sync_status;
                if ($sync_status != config('const.SYNC_STATUS.FAILED')) {
                    $reviewReply->scheduled_sync_time = null;
                }
                $reviewReply->sync_time  = Carbon::now();
                $reviewReply->update();


                $review = Review::where('review_id', '=', $review_id)
                                ->first();

                if ($review != null) {

                    $review->sync_status  = $sync_status;
                    if ($sync_status != config('const.SYNC_STATUS.FAILED')) {
                        $review->scheduled_sync_time = null;
                        // 過去のクチコミに返信した場合も、返信ステータス＝済にする 2021.06.18
                        $review->gmb_review_reply_comment = $rs["gmb_comment"];
                        $review->gmb_review_reply_update_time = Carbon::now();
                    }
                    $review->sync_time  = Carbon::now();
                    $review->update();
                }

                DB::commit();
            }

        }catch(Exception $e){
            DB::rollBack();

            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiReviewRegistService._saveReviewReply";
            $this->_detail = sprintf("review_reply_id=%d", $rs["review_reply_id"]);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            // 開放
            $reviewReply = null;
        }
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