<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\Location;
use App\LocalPost;
use App\MediaItem;
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
use Google_Service_MyBusiness_MediaItem;
use Google_Service_MyBusiness_LocationAssociation;
use Google_Service_MyBusiness_Review;
use Google_Service_MyBusiness_ReviewReply;
use Google_Service_Exception;
use Carbon\Carbon;

class GmbApiLocalpostRegistService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_started_at;
    private $_ended_at;
    private $_localpost_regist_count;
    private $_localpost_delete_count;
    private $_mediaitem_regist_count;
    private $_mediaitem_delete_count;
    
    public function __construct()
    {
        // for debug
     //   DB::enableQueryLog();
    }

    // 投稿の登録・変更・削除
    public function registLocalposts($gmbService, $gmbApiService)
    { 
        $this->_kubun = 0;
        $this->_proc_exit = 0;
        $this->_class_function = "GmbApiLocalpostRegistService.registLocalposts";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_localpost_regist_count = 0;
        $this->_localpost_delete_count = 0;
        $this->_localpost_exception_count = 0;
        $this->_mediaitem_regist_count = 0;
        $this->_mediaitem_delete_count = 0;

        $dsLocalPost = LocalPost::queued()
                                ->where('scheduled_sync_time', '<=', Carbon::now())
                                ->orderBy('gmb_account_id', 'asc')->orderBy('gmb_location_id', 'asc')->orderBy('gmb_location_id', 'asc')
                                ->get();

        foreach($dsLocalPost as $rsLocalPost){
            if ($rsLocalPost->sync_type == config('const.SYNC_TYPE.CREATE') || $rsLocalPost->sync_type == config('const.SYNC_TYPE.PATCH')) {
                // 登録・変更
                $this->_createLocalpost($gmbService, $gmbApiService, $rsLocalPost);
            } else if ($rsLocalPost->sync_type == config('const.SYNC_TYPE.DELETE')) {
                // 削除
                $this->_deleteLocalpost($gmbService, $gmbApiService, $rsLocalPost);
            }
        }

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("localpost_regist_count=%d, mediaitem_regist_count=%d, localpost_delete_count=%d, mediaitem_delete_count=%d, localpost_exception_count=%d", 
                                $this->_localpost_regist_count, $this->_mediaitem_regist_count, $this->_localpost_delete_count, $this->_mediaitem_delete_count, $this->_localpost_exception_count);
        $this->_logging($gmbApiService);
    }

    // 投稿の登録・変更
    private function _createLocalpost($gmbService, $gmbApiService, $rsLocalPost) {

        $local_post_id= $rsLocalPost->local_post_id;
        $gmbAccountId= $rsLocalPost->gmb_account_id;
        $gmbLocationId = $rsLocalPost->gmb_location_id;
        $parent = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);
        $this->_debug('登録 ' .$parent);

        $newPost = new Google_Service_MyBusiness_LocalPost();
        $callToAction = new Google_Service_MyBusiness_CallToAction();
        $localPostEvent = new Google_Service_MyBusiness_LocalPostEvent();
        $startDate = new Google_Service_MyBusiness_Date();
        $startTime = new Google_Service_MyBusiness_TimeOfDay();
        $endDate = new Google_Service_MyBusiness_Date();
        $endTime = new Google_Service_MyBusiness_TimeOfDay();
        $timeInterval = new Google_Service_MyBusiness_TimeInterval();
        $localPostOffer = new Google_Service_MyBusiness_LocalPostOffer();

        $params = [];
        // patchメソッドで要指定
        // $params['updateMask'] = "languageCode,summary,callToAction,event,media,topicType,offer";

        $newPost->setLanguageCode($rsLocalPost->gmb_language_code);
        $newPost->setSummary($rsLocalPost->gmb_summary);
    
        // アクション
        if ($rsLocalPost->gmb_action_type != config('const.ACTION_TYPE.ACTION_TYPE_UNSPECIFIED')) {
            $callToAction->setActionType($rsLocalPost->gmb_action_type);
            $callToAction->setUrl($rsLocalPost->gmb_action_type_url);
            $newPost->setCallToAction($callToAction);
        }

        $hasTimeInterval = false;
        $startDtAry = $gmbApiService->convDatetime2String($rsLocalPost->gmb_event_start_time);
        if (count($startDtAry) != 0) {
            $startDate->setYear($startDtAry['year']);
            $startDate->setMonth($startDtAry['month']);
            $startDate->setDay($startDtAry['day']);
            $timeInterval->setStartDate($startDate);
            // 時刻あり
            if ($rsLocalPost->gmb_has_event_time == 1) {
                $startTime->setHours($startDtAry['hour']);
                $startTime->setMinutes($startDtAry['minute']);
                $startTime->setSeconds($startDtAry['second']);
                $startTime->setNanos(0);
                $timeInterval->setStartTime($startTime);
            }

            $hasTimeInterval = true;
        }

        $endDtAry = $gmbApiService->convDatetime2String($rsLocalPost->gmb_event_end_time);
        if (count($endDtAry) != 0) {
            $endDate->setYear($endDtAry['year'] );
            $endDate->setMonth($endDtAry['month']);
            $endDate->setDay($endDtAry['day']);
            $timeInterval->setEndDate($endDate);
            // 時刻あり
            if ($rsLocalPost->gmb_has_event_time == 1) {
                $endTime->setHours($endDtAry['hour']);
                $endTime->setMinutes($endDtAry['minute']);
                $endTime->setSeconds($endDtAry['second']);
                $endTime->setNanos(0);  
                $timeInterval->setEndTime($endTime);
            }
            $hasTimeInterval = true;
        }

        if ($rsLocalPost->gmb_topic_type == config('const.TOPIC_TYPE.EVENT')) {
            $localPostEvent->setTitle($rsLocalPost->gmb_event_title);
            if ($hasTimeInterval) $localPostEvent->setSchedule($timeInterval);
            $newPost->setEvent($localPostEvent);
        }

        $newPost->setTopicType($rsLocalPost->gmb_topic_type);
        //  $newPost->setAlertType($rsLocalPost->gmb_alert_type);

        /*
        $localPostOffer->setCouponCode($rsLocalPost->gmb_offer_coupon_code);
        $localPostOffer->setRedeemOnlineUrl($rsLocalPost->gmb_offer_redeem_online_url);
        $localPostOffer->setTermsConditions($rsLocalPost->gmb_offer_terms_conditions);
        $newPost->setOffer($localPostOffer);
        */

        // media_items 抽出
        $dsMediaItem = MediaItem::active()->queued()
                                ->where('local_post_id', '=', $rsLocalPost->local_post_id)
                                ->get();
        $has_media_item = false;
        foreach($dsMediaItem as $rsMediaItem){

            $media = new Google_Service_MyBusiness_MediaItem();
            $media->setMediaFormat($rsMediaItem->gmb_media_format);
            /*
            $locationAssociation = new Google_Service_MyBusiness_LocationAssociation();
            $locationAssociation->setCategory($rsMediaItem->gmb_location_association_category);
            $media->setLocationAssociation($locationAssociation);
            */
            $media->setDescription($rsMediaItem->gmb_description);
            $media->setSourceUrl($rsMediaItem->gmb_source_url);
            $newPost->setMedia($media);
            $has_media_item = true;
            // 現在は画像は１つのみ
            break;
        }

        try {

            //$this->_debug($newPost);
            $gmb = $gmbService->accounts_locations_localPosts->create($parent, $newPost);
            //$this->_debug('-----------------------');
            //$this->_debug($gmb);
            if ($gmb) {
                // 投稿成功
                $localPost = LocalPost::where('local_post_id', '=', $local_post_id)
                                        ->first();
                if ($localPost != null) {
                    $nameAry = explode("/", $gmb['name']);
                    $localPost->gmb_local_post_id  = $nameAry[5];

                    $localPost->sync_status  = config('const.SYNC_STATUS.SYNCED');
                    $localPost->sync_time  = Carbon::now();
                    $localPost->scheduled_sync_time = null;
                    $localPost->update_user_id  = 0;
                    $localPost->save();
                    $this->_localpost_regist_count ++;

                    if ($has_media_item) {
                        $mediaItem = MediaItem::where('local_post_id', '=', $local_post_id)
                                                ->first();
                        if ($mediaItem != null) {
                            $mediaItem->sync_status  = config('const.SYNC_STATUS.SYNCED');
                            $mediaItem->sync_time  = Carbon::now();
                            $mediaItem->scheduled_sync_time = null;
                            $mediaItem->update_user_id  = 0;
                            $mediaItem->save();
                            $this->_mediaitem_regist_count ++;
                        }
                    }

                    unset($nameAry);
                }
            }
            unset($gmb);
                
        } catch ( Google_Service_Exception $e ) {
            $this->_localpost_exception_count ++;

            // ログ
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiLocalpostRegistService._createLocalpost";
            $this->_detail = sprintf("parent=%s", $parent);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

            // 投稿失敗
            $localPost = LocalPost::where('local_post_id', '=', $local_post_id)
                                    ->first();
            if ($localPost != null) {
                $localPost->sync_status  = config('const.SYNC_STATUS.CANCEL');
                $localPost->sync_time  = Carbon::now();
                $localPost->scheduled_sync_time = null;
                $localPost->update_user_id  = 0;
                $localPost->save();
            }
    
            $mediaItem = MediaItem::where('local_post_id', '=', $local_post_id)
                                    ->first();
            if ($mediaItem != null) {
                $mediaItem->sync_status  = config('const.SYNC_STATUS.CANCEL');
                $mediaItem->sync_time  = Carbon::now();
                $mediaItem->scheduled_sync_time = null;
                $mediaItem->update_user_id  = 0;
                $mediaItem->save();
            }

        } finally {
            $localPost = null;
        }
    }

    // 投稿の削除
    private function _deleteLocalpost($gmbService, $gmbApiService, $rsLocalPost) {

        $local_post_id= $rsLocalPost->local_post_id;
        $gmbAccountId= $rsLocalPost->gmb_account_id;
        $gmbLocationId = $rsLocalPost->gmb_location_id;
        $gmbLocalPostId = $rsLocalPost->gmb_local_post_id;
        $parent = sprintf('accounts/%s/locations/%s/localPosts/%s', $gmbAccountId, $gmbLocationId, $gmbLocalPostId);
        $this->_debug('削除 ' .$parent);

        try {

            $gmb = $gmbService->accounts_locations_localPosts->delete($parent);
            if (empty($gmb)) {
                // If successful, the response body will be empty.
                $localPost = LocalPost::where('local_post_id', '=', $local_post_id)
                                        ->first();
                if ($localPost != null) {
                    $localPost->is_deleted  = 1;
                    $localPost->sync_status  = config('const.SYNC_STATUS.SYNCED');
                    $localPost->sync_time  = Carbon::now();
                    $localPost->scheduled_sync_time = null;
                    $localPost->update_user_id  = 0;
                    $localPost->save();
                    $this->_localpost_delete_count ++;

                    // media_items 抽出
                    $dsMediaItem = MediaItem::where('local_post_id', '=', $local_post_id)
                                            ->get();

                    foreach($dsMediaItem as $rsMediaItem){
                        $this->_deleteMediaItem($gmbService, $gmbApiService, $rsMediaItem);
                        unset($rsMediaItem);
                    }

                    unset($dsMediaItem);
                }
            }
            unset($gmb);

        } catch ( Google_Service_Exception $e ) {
            $this->_localpost_exception_count ++;

            // ログ
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiLocalpostRegistService._deleteLocalpost";
            $this->_detail = sprintf("parent=%s", $parent);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $localPost = null;
        }
    }

    // 投稿画像の削除
    private function _deleteMediaItem($gmbService, $gmbApiService, $rsMediaItem) {

        $mediaItemId= $rsMediaItem->media_item_id;
        $gmbAccountId= $rsMediaItem->gmb_account_id;
        $gmbLocationId = $rsMediaItem->gmb_location_id;
        $gmbMediaKey = $rsMediaItem->gmb_media_key;
        $parent = sprintf('accounts/%s/locations/%s/media/%s', $gmbAccountId, $gmbLocationId, $gmbMediaKey);
        //$this->_debug('削除 ' .$parent);

        try {

            $gmb = $gmbService->accounts_locations_media->delete($parent);
            if (empty($gmb)) {
                // If successful, the response body will be empty.
                $mediaItem = MediaItem::where('media_item_id', '=', $mediaItemId)
                                        ->first();
                if ($mediaItem != null) {
                    $mediaItem->is_deleted  = 1;
                    $mediaItem->sync_status  = config('const.SYNC_STATUS.SYNCED');
                    $mediaItem->sync_time  = Carbon::now();
                    $mediaItem->scheduled_sync_time = null;
                    $mediaItem->update_user_id  = 0;
                    $mediaItem->save();
                    $this->_mediaitem_delete_count ++;
                }
            }
            unset($gmb);

        } catch ( Google_Service_Exception $e ) {
            // ログ
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiLocalpostRegistService._deleteMediaItem";
            $this->_detail = sprintf("parent=%s", $parent);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $mediaItem = null;
        }
    }








    // 未使用
    // 投稿の登録・変更・削除
    // イベント追加テスト用
    public function registLocalposts_イベント追加($gmbService, $gmbApiService)
    { 

        $this->_debug('registLocalposts');

        $dsLocalPost = LocalPost::queued()
                                ->where('scheduled_sync_time', '<=', Carbon::now())
                                ->get();

        foreach($dsLocalPost as $rsLocalPost){

            $gmbAccountId= $rsLocalPost->gmb_account_id;
            $gmbLocationId = $rsLocalPost->gmb_location_id;
            $parent = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);

            $this->_debug($parent);

            $newPost = new Google_Service_MyBusiness_LocalPost();
            $callToAction = new Google_Service_MyBusiness_CallToAction();
            $localPostEvent = new Google_Service_MyBusiness_LocalPostEvent();
            $startDate = new Google_Service_MyBusiness_Date();
            $startTime = new Google_Service_MyBusiness_TimeOfDay();
            $endDate = new Google_Service_MyBusiness_Date();
            $endTime = new Google_Service_MyBusiness_TimeOfDay();
            $timeInterval = new Google_Service_MyBusiness_TimeInterval();
            $localPostOffer = new Google_Service_MyBusiness_LocalPostOffer();

            $params = [];
            // TODO
            // patchメソッドで要指定
           // $params['updateMask'] = "languageCode,summary,callToAction,event,media,topicType,offer";
           // $params['updateMask'] = "languageCode,summary,callToAction, topicType";

            $newPost->setLanguageCode($rsLocalPost->gmb_language_code);
            $newPost->setSummary($rsLocalPost->gmb_summary);
    
         // アクションがある場合   
            $callToAction->setActionType($rsLocalPost->gmb_action_type);
            $callToAction->setUrl($rsLocalPost->gmb_action_type_url);
            $newPost->setCallToAction($callToAction);
            
            $localPostEvent->setTitle($rsLocalPost->gmb_event_title);

            $hasTimeInterval = false;
            $startDtAry = $gmbApiService->convDatetime2String($rsLocalPost->gmb_event_start_time);
            if (count($startDtAry) != 0) {
                $startDate->setYear($startDtAry['year']);
                $startDate->setMonth($startDtAry['month']);
                $startDate->setDay($startDtAry['day']);
                $timeInterval->setStartDate($startDate);

                /*
                $startTime->setHours($startDtAry['hour']);
                $startTime->setMinutes($startDtAry['minute']);
                $startTime->setSeconds($startDtAry['second']);
                $startTime->setNanos(0);           
                $timeInterval->setStartTime($startTime);
                */
                $hasTimeInterval = true;
            }

            $endDtAry = $gmbApiService->convDatetime2String($rsLocalPost->gmb_event_end_time);
            if (count($endDtAry) != 0) {
                $endDate->setYear($endDtAry['year'] );
                $endDate->setMonth($endDtAry['month']);
                $endDate->setDay($endDtAry['day']);
                $timeInterval->setEndDate($endDate);

                /*
                $endTime->setHours($endDtAry['hour']);
                $endTime->setMinutes($endDtAry['minute']);
                $endTime->setSeconds($endDtAry['second']);
                $endTime->setNanos(0);  
                $timeInterval->setEndTime($endTime);
                */
                $hasTimeInterval = true;
            }

            if ($hasTimeInterval) $localPostEvent->setSchedule($timeInterval);
            $newPost->setEvent($localPostEvent);
            
            
            // media_items 抽出
            /*
            $dsMediaItem = MediaItem::active()->queued()
                                    ->where('local_post_id', '=', $rsLocalPost->local_post_id)
                                    ->get();
            
            */
            $dsMediaItem = MediaItem::active()->queued()
                                    ->where('local_post_id', '=', 1)
                                    ->get(); 
            

            foreach($dsMediaItem as $rsMediaItem){

                $media = new Google_Service_MyBusiness_MediaItem();
                $media->setMediaFormat($rsMediaItem->gmb_media_format);
                /*
                $locationAssociation = new Google_Service_MyBusiness_LocationAssociation();
                $locationAssociation->setCategory($rsMediaItem->gmb_location_association_category);
                $media->setLocationAssociation($locationAssociation);
                */
                $media->setDescription($rsMediaItem->gmb_description);
                $media->setSourceUrl($rsMediaItem->gmb_source_url);

                $newPost->setMedia($media);
            }
            
            $newPost->setTopicType($rsLocalPost->gmb_topic_type);
         //   $newPost->setAlertType($rsLocalPost->gmb_alert_type);

            /*
            $localPostOffer->setCouponCode($rsLocalPost->gmb_offer_coupon_code);
            $localPostOffer->setRedeemOnlineUrl($rsLocalPost->gmb_offer_redeem_online_url);
            $localPostOffer->setTermsConditions($rsLocalPost->gmb_offer_terms_conditions);
            $newPost->setOffer($localPostOffer);
            */

            logger()->error('parent='.$parent);
          //  logger()->error(print_r($newPost, true));
          //  $this->_debug($newPost);

            
            $gmb = $gmbService->accounts_locations_localPosts->create($parent, $newPost);
          //  $this->_debug($gmb);
            logger()->error(print_r($gmb, true));
            



            
        }
    }

    // 未使用
    // 投稿の登録・変更・削除
    // 新着情報テスト用
    public function registLocalposts_新着情報($gmbService, $gmbApiService)
    { 

        $this->_debug('registLocalposts');

        $dsLocalPost = LocalPost::queued()
                                ->where('scheduled_sync_time', '<=', Carbon::now())
                                ->get();

        foreach($dsLocalPost as $rsLocalPost){

            $gmbAccountId= $rsLocalPost->gmb_account_id;
            $gmbLocationId = $rsLocalPost->gmb_location_id;
            $parent = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);

            $this->_debug($parent);

            $newPost = new Google_Service_MyBusiness_LocalPost();
            $callToAction = new Google_Service_MyBusiness_CallToAction();
            $localPostEvent = new Google_Service_MyBusiness_LocalPostEvent();
            $startDate = new Google_Service_MyBusiness_Date();
            $startTime = new Google_Service_MyBusiness_TimeOfDay();
            $endDate = new Google_Service_MyBusiness_Date();
            $endTime = new Google_Service_MyBusiness_TimeOfDay();
            $timeInterval = new Google_Service_MyBusiness_TimeInterval();
            $localPostOffer = new Google_Service_MyBusiness_LocalPostOffer();

            $params = [];
            // TODO
            // patchメソッドで要指定
           // $params['updateMask'] = "languageCode,summary,callToAction,event,media,topicType,offer";
           // $params['updateMask'] = "languageCode,summary,callToAction, topicType";

            $newPost->setLanguageCode($rsLocalPost->gmb_language_code);
            $newPost->setSummary($rsLocalPost->gmb_summary);
    
            
            $callToAction->setActionType($rsLocalPost->gmb_action_type);
         //   $callToAction->setUrl($rsLocalPost->gmb_action_type_url);
            $newPost->setCallToAction($callToAction);
            

            //$localPostEvent->setTitle($rsLocalPost->gmb_event_title);

            $hasTimeInterval = false;
            /*
            $startDtAry = $gmbApiService->convDatetime2String($rsLocalPost->gmb_event_start_time);
            if (count($startDtAry) != 0) {
                $startDate->setYear($startDtAry['year']);
                $startDate->setMonth($startDtAry['month']);
                $startDate->setDay($startDtAry['day']);
                $timeInterval->setStartDate($startDate);

                $startTime->setHours($startDtAry['hour']);
                $startTime->setMinutes($startDtAry['minute']);
                $startTime->setSeconds($startDtAry['second']);
                $startTime->setNanos(0);           
                $timeInterval->setStartTime($startTime);
                $hasTimeInterval = true;
            }

            $endDtAry = $gmbApiService->convDatetime2String($rsLocalPost->gmb_event_end_time);
            if (count($endDtAry) != 0) {
                $endDate->setYear($endDtAry['year'] );
                $endDate->setMonth($endDtAry['month']);
                $endDate->setDay($endDtAry['day']);
                $timeInterval->setEndDate($endDate);

                $endTime->setHours($endDtAry['hour']);
                $endTime->setMinutes($endDtAry['minute']);
                $endTime->setSeconds($endDtAry['second']);
                $endTime->setNanos(0);  
                $timeInterval->setEndTime($endTime);
                $hasTimeInterval = true;
            }
            */

         //   if ($hasTimeInterval) $localPostEvent->setSchedule($timeInterval);
         //   $newPost->setEvent($localPostEvent);
            
   
            
            // media_items 抽出
            /*
            $dsMediaItem = MediaItem::active()->queued()
                                    ->where('local_post_id', '=', $rsLocalPost->local_post_id)
                                    ->get();
            */
            $dsMediaItem = MediaItem::active()->queued()
                                    ->where('local_post_id', '=', 388)
                                    ->get(); 
                                    

            foreach($dsMediaItem as $rsMediaItem){

                $media = new Google_Service_MyBusiness_MediaItem();
                $media->setMediaFormat($rsMediaItem->gmb_media_format);
                /*
                $locationAssociation = new Google_Service_MyBusiness_LocationAssociation();
                $locationAssociation->setCategory($rsMediaItem->gmb_location_association_category);
                $media->setLocationAssociation($locationAssociation);
                */
                $media->setDescription($rsMediaItem->gmb_description);
                $media->setSourceUrl($rsMediaItem->gmb_source_url);

                $newPost->setMedia($media);

            }
            

         //   $newPost->setTopicType($rsLocalPost->gmb_topic_type);
         //   $newPost->setAlertType($rsLocalPost->gmb_alert_type);

            /*
            $localPostOffer->setCouponCode($rsLocalPost->gmb_offer_coupon_code);
            $localPostOffer->setRedeemOnlineUrl($rsLocalPost->gmb_offer_redeem_online_url);
            $localPostOffer->setTermsConditions($rsLocalPost->gmb_offer_terms_conditions);
            $newPost->setOffer($localPostOffer);
            */

            logger()->error('parent='.$parent);
         //   logger()->error(print_r($newPost, true));


            $gmb = $gmbService->accounts_locations_localPosts->create($parent, $newPost);
         //   $this->_debug($gmb);
            logger()->error(print_r($gmb, true));
        
            
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