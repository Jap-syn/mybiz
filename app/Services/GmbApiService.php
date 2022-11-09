<?php
// 修正
namespace App\Services;

use DB;
use App\LogApi;

use App\Account;
use App\Location;
use App\LocalPost;
use App\MediaItem;
use App\Review;
use App\ReviewReply;
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
use Carbon\Carbon;

class GmbApiService
{
    public function __construct()
    {
    }
    public function newGoogleClient($authFile)
    {
    
        $client = new Google_Client();
        $client->setApplicationName("マイビジチェーン");       // app name
        $client->setApprovalPrompt('force');
        $client->setAccessType('offline');
        $client->setAuthConfig($authFile);
        $client->addScope("https://www.googleapis.com/auth/business.manage");                                                                                        
        // $client->addScope("https://www.googleapis.com/auth/plus.business.manage");
        return $client;
    }

    public function setAccessToken($client, $tokenFile)
    {
        if (file_exists($tokenFile)) {
            $accessToken = json_decode(file_get_contents($tokenFile), true);
            $client->setAccessToken($accessToken);
            return config('command.exit_code.SUCCESS');
    
        } else {
            return config('command.exit_code.ERROR');
        }
    }

    public function refreshToken($client, $tokenFile)
    {
        // リフレッシュするか新しいトークンを取得
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

        } else {

            $code = config('command.auth.code');
            $accessToken = $client->fetchAccessTokenWithAuthCode($code);
            $client->setAccessToken($accessToken);

            // 認証エラー
            if (array_key_exists('error', $accessToken)) {
                //throw new Exception(join(', ', $accessToken));
                return config('command.exit_code.ERROR');
            }
        }

      // トークンの保存
      if (!file_exists($tokenFile)) {
          touch($tokenFile);
          chmod($tokenFile, 0777);
      }

        file_put_contents($tokenFile, json_encode($client->getAccessToken()));
        return config('command.exit_code.SUCCESS');
    }

    // API連携ログ出力
    public function logApiBatch($kubun, 
                                $proc_exit,
                                $class_function,
                                $detail,
                                $exception,
                                $started_at,
                                $ended_at) {

        try {

            $logApi = new LogApi;
            $logApi->kubun          = $kubun;
            $logApi->proc_exit      = $proc_exit;
            $logApi->class_function = $class_function;
            $logApi->detail         = $detail;
            $logApi->exception      = $exception;
            $logApi->started_at     = $started_at;
            $logApi->ended_at       = $ended_at;

            $logApi->save();

        } catch ( Exception $e ) {
            logger()->error(sprintf("logApiBatch Exception: %s", $e->getMessage()));
        } finally {
            $logApi = null;
        }
    }

    public function covertTimezone2Jst($value) {
        $datetime = new Carbon($value, 'UTC');
        $datetime->setTimezone('JST');
        return $datetime;
    }

    public function checkGmbJson($value, $default='') {

        try {
            if ($value == NULL)                     $value = $default;
            else {
                if (is_array($value)) {
                    $value = implode(",", str_replace(',', '、', $value));
                } else {
                    if (strtoupper($value) == 'NULL')  $value = $default;
                }
            }

        } catch ( Exception $e ) {   
            $value = $default;
        }

        return $value;
    }

    public function checkBooleanGmbJson($value) {

        try {

            if (isset($value)) {
                $value = (int)$value;
            } else {
                $value = null;
            }

        } catch ( Exception $e ) {   
            $value = null;
        }

        return $value;
    }

    public function removeTranslatedGoogleString($src) {
        $comment = trim($this->checkGmbJson($src));
      
        return $comment;
    }

    public function checkGmbJsonScheduleTime($date, $time) {

        $datetime = NULL;
        $fmtDate = '';
        $fmtTime = '00:00:00';
        if ($date['year'] != NULL && $date['month'] != NULL && $date['day'] != NULL) {
            if (is_numeric($date['year']) && is_numeric($date['month']) && is_numeric($date['day'])) {
                $format = '%04d-%02d-%02d';
                $fmtDate = sprintf($format, $date['year'], $date['month'], $date['day']);
            }
        }

        if ($time['hours'] != NULL && $time['minutes'] != NULL && $time['seconds'] != NULL) {
            if (is_numeric($time['hours']) && is_numeric($time['minutes']) && is_numeric($time['seconds'])) {
                $format = '%02d:%02d:%02d';
                $fmtTime = sprintf($format, $time['hours'], $time['minutes'], $time['seconds']);
            }
        }

        if ($fmtDate != '') {
            $datetime = DB::raw("STR_TO_DATE('" .$fmtDate  ." " .$fmtTime ."','%Y-%m-%d %H:%i:%s')");
        }

        return $datetime;
    }

    // 日付型データを文字列に変換する
    public function convDatetime2String($datetime) {

        $ary = [];
        if($datetime != NULL && strlen($datetime) == 19) {
            if (substr($datetime, 0, 4) != "0000") {
                $ary['year'] = substr($datetime, 0, 4);
                $ary['month'] = substr($datetime, 5, 2);
                $ary['day'] = substr($datetime, 8, 2);

                $ary['hour'] = substr($datetime, 11, 2);
                $ary['minute'] = substr($datetime, 14, 2);
                $ary['second'] = substr($datetime, 17, 2);
            }
        }

        return $ary;
    }

    public function debug($msg) {
        var_dump($msg);
    }













    // 以下未使用　---------------------------------

    // 店舗情報を更新
    public function updateLocations($gmbService) {

        $location = new Google_Service_MyBusiness_Location();
        
        $dsLocations = Location::select(['location_id', 'gmb_account_id', 'gmb_location_id', 'gmb_profile_description'])
                                ->active()->queued()->get();    
        //dd(DB::getQueryLog());
        foreach($dsLocations as $rsLocations){
            //$this->_debug('更新対象 gmb_location_id='.$rsLocations->gmb_location_id );
            $profile = new Google_Service_MyBusiness_Profile();

            $gmbAccountId= $rsLocations->gmb_account_id;
            $gmbLocationId = $rsLocations->gmb_location_id;

            $params = [];
            $params['updateMask'] = "profile"; // 更新する項目はカンマ区切り
            $profile->setDescription($rsLocations->gmb_profile_description);
            $location->setProfile($profile);

            $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId;
            $gmb = $gmbService->accounts_locations->patch($name, $location, $params);
            //$this->_debug($gmb);
            if ($gmb) {
                // 処理結果をsync_statusに設定する
                $sync_status = config('const.SYNC_STATUS.FAILED');
                if (isset($gmb['profile']['description']) && $gmb['profile']['description'] != NULL) {
                    $sync_status = config('const.SYNC_STATUS.SYNCED');
                }

                try {
                    $rs = Location::where('location_id', $rsLocations->location_id)->first();
                    //dd(DB::getQueryLog());
                    //$this->_debug('更新結果：'.$sync_status );
                    $rs->sync_status = $sync_status;
                    $rs->sync_time = Carbon::now();
                    $rs->save();

                } catch ( Exception $e ) {  
                    logger()->error('updateLocations Exception: ' . $name .'  ' .$e->getMessage());
                }
            }
        }
    }

    // 投稿を更新
    public function updateLocalPosts($gmbService) {

        logger()->error('updateLocalPosts start');

        DB::enableQueryLog();

        // is_deleted=1の場合、DELETEで連携する必要があるので、
        // sync_type, sync_status=sync_status, scheduled_sync_timeが連携対象になる。
        $dsLocalPost = LocalPost::queued()
                                ->where('scheduled_sync_time', '<=', Carbon::now())
                                ->get();

        foreach($dsLocalPost as $rsLocalPost){

            $gmbAccountId= $rsLocalPost->gmb_account_id;
            $gmbLocationId = $rsLocalPost->gmb_location_id;
            $parent = sprintf('accounts/%d/locations/%d', $gmbAccountId, $gmbLocationId);

            $newPost = new Google_Service_MyBusiness_LocalPost();
            $callToAction = new Google_Service_MyBusiness_CallToAction();
            $localPostEvent = new Google_Service_MyBusiness_LocalPostEvent();
            $startDate = new Google_Service_MyBusiness_Date();
            $startTime = new Google_Service_MyBusiness_TimeOfDay();
            $endDate = new Google_Service_MyBusiness_Date();
            $endTime = new Google_Service_MyBusiness_TimeOfDay();
            $timeInterval = new Google_Service_MyBusiness_TimeInterval();
            $localPostOffer = new Google_Service_MyBusiness_LocalPostOffer();
            $localPostProduct = new Google_Service_MyBusiness_LocalPostProduct();

            $params = [];
            // TODO
            // patchメソッドで要指定
           // $params['updateMask'] = "languageCode,summary,callToAction,event,media,topicType,offer";

            $newPost->setLanguageCode($rsLocalPost->gmb_language_code);
            $newPost->setSummary($rsLocalPost->gmb_summary);
    
            $callToAction->setActionType($rsLocalPost->gmb_action_type);
            $callToAction->setUrl($rsLocalPost->gmb_action_type_url);
            $newPost->setCallToAction($callToAction);
            
            /*
            $localPostEvent->setTitle($rsLocalPost->gmb_event_title);

            $hasTimeInterval = false;
            $startDtAry = $this->_convDatetime2String($rsLocalPost->gmb_event_start_time);
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

            $endDtAry = $this->_convDatetime2String($rsLocalPost->gmb_event_end_time);
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

            if ($hasTimeInterval) $localPostEvent->setSchedule($timeInterval);
            $newPost->setEvent($localPostEvent);
            */
   
            /*
            // media_items 抽出
            $dsMediaItem = MediaItem::active()->queued()
                                    ->where('local_post_id', '=', $rsLocalPost->local_post_id)
                                    ->get();
            foreach($dsMediaItem as $rsMediaItem){

                $media = new Google_Service_MyBusiness_MediaItem();
                $media->setMediaFormat($rsMediaItem->gmb_media_format);

                $locationAssociation = new Google_Service_MyBusiness_LocationAssociation();
                $locationAssociation->setCategory($rsMediaItem->gmb_location_association_category);
                $media->setLocationAssociation($locationAssociation);

              //  $media->setDescription($rsMediaItem->gmb_description);
                $media->setSourceUrl($rsMediaItem->gmb_source_url);

                $newPost->setMedia($media);
            }
            */

            $newPost->setTopicType($rsLocalPost->gmb_topic_type);
            //$newPost->setAlertType($rsLocalPost->gmb_alert_type);

            /*
            $localPostOffer->setCouponCode($rsLocalPost->gmb_offer_coupon_code);
            $localPostOffer->setRedeemOnlineUrl($rsLocalPost->gmb_offer_redeem_online_url);
            $localPostOffer->setTermsConditions($rsLocalPost->gmb_offer_terms_conditions);
            $newPost->setOffer($localPostOffer);
            */

            logger()->error('parent='.$parent);
            logger()->error(print_r($params, true));
            logger()->error('localPost->local_post_id='.$rsLocalPost->local_post_id);
            logger()->error(print_r($newPost, true));

          //  $gmb = $gmbService->accounts_locations_localPosts->create($parent, $newPost, $params);
            $gmb = $gmbService->accounts_locations_localPosts->create($parent, $newPost);

            logger()->error(print_r($gmb, true));

            /*
            if ($gmb) {
                // 処理結果をsync_statusに設定する
                $sync_status = config('const.SYNC_STATUS.FAILED');

                $gmb_local_post_id = "";
                if (isset($gmb['name']) && $gmb['name'] != "") {
                    $gmb_local_post_id = preg_replace("/^.*localPosts\//","",$gmb['name']);
                    $sync_status = config('const.SYNC_STATUS.SYNCED');
                }
                $this->_debug('更新結果： local_post_id='.$rsLocalPost->local_post_id ."  sync_status=" .$sync_status );

                try {

                    DB::beginTransaction();

                    $rsLPost = LocalPost::where('local_post_id', $rsLocalPost->local_post_id)->first();
                    $rsLPost->gmb_local_post_id = $gmb_local_post_id;
                    $rsLPost->sync_status = $sync_status;
                    $rsLPost->sync_time = Carbon::now();
                    $rsLPost->save();

                    // TODO local_post_idごとにgmb_media_keyに設定すること
                    if (isset($gmb['media'])) {
                        $gmb_media_key = preg_replace("/^.*media\//","",$gmb['media'][0]['name']);

                        MediaItem::where('local_post_id', $rsLocalPost->local_post_id)
                                    ->update ([
                                        'gmb_media_key' => $gmb_media_key,
                                        'sync_status' => $sync_status,
                                        'sync_time' => Carbon::now(),
                                        'update_time' => Carbon::now(),
                                        'update_user_id' => 0
                                    ]);
                    }

                    DB::commit();

                } catch ( Exception $e ) { 
                    DB::rollBack();
                    logger()->error('updateLocalPosts Exception: ' . $name .'  ' .$e->getMessage());
                }
            }
            */
        }
    
        logger()->error(DB::getQueryLog());
        logger()->error('updateLocalPosts end');

    }

    public function updateLocalPosts_event($gmbService) {

        logger()->error('updateLocalPosts start');

        DB::enableQueryLog();

        // is_deleted=1の場合、DELETEで連携する必要があるので、
        // sync_type, sync_status=sync_status, scheduled_sync_timeが連携対象になる。
        $dsLocalPost = LocalPost::queued()
                                ->where('scheduled_sync_time', '<=', Carbon::now())
                                ->get();

        foreach($dsLocalPost as $rsLocalPost){

            $gmbAccountId= $rsLocalPost->gmb_account_id;
            $gmbLocationId = $rsLocalPost->gmb_location_id;
            $parent = sprintf('accounts/%d/locations/%d', $gmbAccountId, $gmbLocationId);

            $newPost = new Google_Service_MyBusiness_LocalPost();
            $callToAction = new Google_Service_MyBusiness_CallToAction();
            $localPostEvent = new Google_Service_MyBusiness_LocalPostEvent();
            $startDate = new Google_Service_MyBusiness_Date();
            $startTime = new Google_Service_MyBusiness_TimeOfDay();
            $endDate = new Google_Service_MyBusiness_Date();
            $endTime = new Google_Service_MyBusiness_TimeOfDay();
            $timeInterval = new Google_Service_MyBusiness_TimeInterval();
            $localPostOffer = new Google_Service_MyBusiness_LocalPostOffer();
            $localPostProduct = new Google_Service_MyBusiness_LocalPostProduct();

            $params = [];
            // TODO
            // patchメソッドで要指定
           // $params['updateMask'] = "languageCode,summary,callToAction,event,media,topicType,offer";

            $newPost->setLanguageCode($rsLocalPost->gmb_language_code);
            $newPost->setSummary($rsLocalPost->gmb_summary);
    
            
            $callToAction->setActionType($rsLocalPost->gmb_action_type);
            $callToAction->setUrl($rsLocalPost->gmb_action_type_url);
            $newPost->setCallToAction($callToAction);
            
           $localPostEvent->setTitle($rsLocalPost->gmb_event_title);

            $hasTimeInterval = false;
            $startDtAry = $this->_convDatetime2String($rsLocalPost->gmb_event_start_time);
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

            $endDtAry = $this->_convDatetime2String($rsLocalPost->gmb_event_end_time);
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

            if ($hasTimeInterval) $localPostEvent->setSchedule($timeInterval);
            $newPost->setEvent($localPostEvent);
   
            // media_items 抽出
            $dsMediaItem = MediaItem::active()->queued()
                                    ->where('local_post_id', '=', $rsLocalPost->local_post_id)
                                    ->get();
            foreach($dsMediaItem as $rsMediaItem){

                $media = new Google_Service_MyBusiness_MediaItem();
                $media->setMediaFormat($rsMediaItem->gmb_media_format);

                $locationAssociation = new Google_Service_MyBusiness_LocationAssociation();
                $locationAssociation->setCategory($rsMediaItem->gmb_location_association_category);
                $media->setLocationAssociation($locationAssociation);

              //  $media->setDescription($rsMediaItem->gmb_description);
                $media->setSourceUrl($rsMediaItem->gmb_source_url);

                $newPost->setMedia($media);
            }

            $newPost->setTopicType($rsLocalPost->gmb_topic_type);
            //$newPost->setAlertType($rsLocalPost->gmb_alert_type);

            /*
            $localPostOffer->setCouponCode($rsLocalPost->gmb_offer_coupon_code);
            $localPostOffer->setRedeemOnlineUrl($rsLocalPost->gmb_offer_redeem_online_url);
            $localPostOffer->setTermsConditions($rsLocalPost->gmb_offer_terms_conditions);
            $newPost->setOffer($localPostOffer);
            */

            logger()->error('parent='.$parent);
            logger()->error(print_r($params, true));
            logger()->error('localPost->local_post_id='.$rsLocalPost->local_post_id);
            logger()->error(print_r($newPost, true));

          //  $gmb = $gmbService->accounts_locations_localPosts->create($parent, $newPost, $params);
            $gmb = $gmbService->accounts_locations_localPosts->create($parent, $newPost);

            logger()->error(print_r($gmb, true));

            if ($gmb) {
                // 処理結果をsync_statusに設定する
                $sync_status = config('const.SYNC_STATUS.FAILED');

                $gmb_local_post_id = "";
                if (isset($gmb['name']) && $gmb['name'] != "") {
                    $gmb_local_post_id = preg_replace("/^.*localPosts\//","",$gmb['name']);
                    $sync_status = config('const.SYNC_STATUS.SYNCED');
                }
                $this->_debug('更新結果： local_post_id='.$rsLocalPost->local_post_id ."  sync_status=" .$sync_status );

                try {

                    DB::beginTransaction();

                    $rsLPost = LocalPost::where('local_post_id', $rsLocalPost->local_post_id)->first();
                    $rsLPost->gmb_local_post_id = $gmb_local_post_id;
                    $rsLPost->sync_status = $sync_status;
                    $rsLPost->sync_time = Carbon::now();
                    $rsLPost->save();

                    // TODO local_post_idごとにgmb_media_keyに設定すること
                    if (isset($gmb['media'])) {
                        $gmb_media_key = preg_replace("/^.*media\//","",$gmb['media'][0]['name']);

                        MediaItem::where('local_post_id', $rsLocalPost->local_post_id)
                                    ->update ([
                                        'gmb_media_key' => $gmb_media_key,
                                        'sync_status' => $sync_status,
                                        'sync_time' => Carbon::now(),
                                        'update_time' => Carbon::now(),
                                        'update_user_id' => 0
                                    ]);
                    }

                    DB::commit();

                } catch ( Exception $e ) { 
                    DB::rollBack();
                    logger()->error('updateLocalPosts Exception: ' . $name .'  ' .$e->getMessage());
                }
            }
       
        }
    
        logger()->error(DB::getQueryLog());
        logger()->error('updateLocalPosts end');

    }

    // 店舗のすべての投稿情報  削除してOK
    public function getLocalPosts($gmbService, $gmbAccountId, $gmbLocationId)
    { 
        $location = Location::active()
                            ->where('gmb_account_id', '=', $gmbAccountId)
                            ->where('gmb_location_id', '=', $gmbLocationId)
                            ->first();

        if ($location != NULL) {
            $accountId = $location->account_id;
            $locationId = $location->location_id;

            $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId;
            $gmb = $gmbService->accounts_locations_localPosts->listAccountsLocationsLocalPosts($name);
            if ($gmb) {

                foreach($gmb['localPosts'] as $localPost){
                    $this->_getLocalPost($accountId, $locationId, $localPost);
                }
    
                do{
                    if (isset($gmb['nextPageToken'])) {
                        $optParams = array('pageToken' => $gmb['nextPageToken']);
                        $gmb = $gmbService->accounts_locations->listAccountsLocations($name, $optParams);
                        if ($gmb)  {
                            foreach($gmb['localPosts'] as $localPost){
                                $this->_getLocalPost($accountId, $locationId, $localPost);
                            }
                        } else {
                            break;
                        }
                    }

                } while(isset($gmb['nextPageToken']));
            }
        }
    }

    // 特定の投稿情報　削除してOK
    public function getLocalPost($gmbService, $gmbAccountId, $gmbLocationId, $gmbLocalPostId)
    { 
        $location = Location::active()
                            ->where('gmb_account_id', '=', $gmbAccountId)
                            ->where('gmb_location_id', '=', $gmbLocationId)
                            ->first();
        //dd(DB::getQueryLog());
        if ($location != NULL) {
            $accountId = $location->account_id;
            $locationId = $location->location_id;
            
            $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId .'/localPosts/' .$gmbLocalPostId;
            $gmb = $gmbService->accounts_locations_localPosts->get($name);
            if ($gmb) {
                $this->_getLocalPost($accountId, $locationId, $gmb);
            }
        }
    }

    // 投稿情報を取得
    private function _getLocalPost($accountId, $locationId, $gmb)
    {
        // accounts/103433320649253515986/locations/6411054713785239288/localPosts/3688916939302625456
        $nameAry = explode("/", $gmb['name']);
        $localPost = LocalPost::active()
                            ->where('gmb_account_id', '=', $nameAry[1])
                            ->where('gmb_location_id', '=', $nameAry[3])
                            ->where('gmb_local_post_id', '=', $nameAry[5])
                            ->first();
        //dd(DB::getQueryLog());
        if ($localPost == NULL) {
            //$this->_debug('新規作成 ' .$gmb['name']);
            $localPost = new LocalPost;
            $this->_syncLocalPost($locationId, 0, $localPost, $gmb);

        } else {
            //$this->_debug('更新 ' .$gmb['name']);
            if ($localPost->sync_status == config('const.SYNC_STATUS.SYNCED') || $localPost->sync_status == config('const.SYNC_STATUS.FAILED')) {
                $this->_syncLocalPost($locationId, $localPost->local_post_id, $localPost, $gmb);
            }
        }
    }

    // 投稿情報の同期
    private function _syncLocalPost($locationId, $localPostId, $localPost, $gmb)
    {

        try {

            DB::beginTransaction();

            $localPost->location_id  = $locationId;

            // accounts/103433320649253515986/locations/6411054713785239288/localPosts/3688916939302625456
            $nameAry = explode("/", $gmb['name']);
            $localPost->gmb_account_id  = $nameAry[1];
            $localPost->gmb_location_id  = $nameAry[3];
            $localPost->gmb_local_post_id  = $nameAry[5];

            $localPost->gmb_language_code  = $this->_checkGmbJson($gmb['languageCode']);
            $localPost->gmb_summary  = $this->_checkGmbJson($gmb['summary']);
            $localPost->gmb_action_type  = $this->_checkGmbJson($gmb['callToAction']['actionType'], 'LEARN_MORE');
            $localPost->gmb_action_type_url  = $this->_checkGmbJson($gmb['callToAction']['url']);

            $localPost->gmb_create_time  = $this->_checkGmbJson($gmb['createTime']); 
            $localPost->gmb_update_time  = $this->_checkGmbJson($gmb['updateTime']);

            $localPost->gmb_event_title  = $this->_checkGmbJson($gmb['event']['title']);
            $localPost->gmb_event_start_time  = $this->_checkGmbJsonScheduleTime($gmb['event']['schedule']['startDate'],$gmb['event']['schedule']['startTime']); 
            $localPost->gmb_event_end_time  = $this->_checkGmbJsonScheduleTime($gmb['event']['schedule']['endDate'],$gmb['event']['schedule']['endTime']); 

            $localPost->gmb_local_post_state  = $this->_checkGmbJson($gmb['state'], 'LIVE');
            $localPost->gmb_search_url  = $this->_checkGmbJson($gmb['searchUrl']);
            $localPost->gmb_topic_type  = $this->_checkGmbJson($gmb['topicType'], 'EVENT');

            $localPost->gmb_offer_coupon_code  = $this->_checkGmbJson($gmb['offer']['couponCode']);
            $localPost->gmb_offer_redeem_online_url  = $this->_checkGmbJson($gmb['offer']['redeemOnlineUrl']);
            $localPost->gmb_offer_terms_conditions  = $this->_checkGmbJson($gmb['offer']['termsConditions']);

            $localPost->is_deleted  = 0;
            $localPost->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $localPost->create_user_id  = 0;

            $localPost->save();

            if ($localPostId == 0) {
                $localPostId = $localPost->local_post_id;
            }

            // 投稿時の写真を同期
            MediaItem::active()
                        ->where('gmb_account_id', '=', $nameAry[1])
                        ->where('gmb_location_id', '=', $nameAry[3])
                        ->update([
                            'is_deleted' => config('const.FLG_ON')
                        ]);

            foreach($gmb['media'] as $media){
                // accounts/103433320649253515986/locations/6411054713785239288/media/localPosts/AF1QipNhNgZw_hWjrTpx17oOTZrXaozkmZbQsHcLFHQ"
                $media_nameAry = explode("/", $media['name']);
                $mediaItem = MediaItem::where('gmb_account_id', '=', $media_nameAry[1])
                                    ->where('gmb_location_id', '=', $media_nameAry[3])
                                    ->where('gmb_media_key', '=', $media_nameAry[5] .'/' .$media_nameAry[6])
                                    ->first();
                //dd(DB::getQueryLog());
                if ($mediaItem == NULL) {
                    //$this->_debug('新規作成 ' .$gmb['name']);
                    $mediaItem = new MediaItem;
                    $this->_syncMediaItem($localPostId, $mediaItem, $media);

                } else {
                    //$this->_debug('更新 ' .$gmb['name']);
                    if ($mediaItem->sync_status == config('const.SYNC_STATUS.SYNCED') || $mediaItem->sync_status == config('const.SYNC_STATUS.FAILED')) {
                        $this->_syncMediaItem($localPostId, $mediaItem, $media);
                    }
                }
            }

            DB::commit();

        } catch ( Exception $e ) {
            DB::rollBack();
            logger()->error($e->getMessage());
        }
    }

    // 投稿時の写真を同期
    private function _syncMediaItem($localPostId, $mediaItem, $gmb)
    {

        $mediaItem->local_post_id  = $localPostId;

        // accounts/103433320649253515986/locations/6411054713785239288/media/localPosts/AF1QipNhNgZw_hWjrTpx17oOTZrXaozkmZbQsHcLFHQ"
        $nameAry = explode("/", $gmb['name']);
        $mediaItem->gmb_account_id  = $nameAry[1];
        $mediaItem->gmb_location_id  = $nameAry[3]; 
        $mediaItem->gmb_media_key  = $nameAry[5] .'/' .$nameAry[6]; 

        $mediaItem->gmb_media_format  = $this->_checkGmbJson($gmb['mediaFormat'], 'MEDIA_FORMAT_UNSPECIFIED'); 
        $mediaItem->gmb_location_association_category  = $this->_checkGmbJson($gmb['locationAssociation']['category'], 'CATEGORY_UNSPECIFIED'); 
        $mediaItem->gmb_location_association_price_list_item_id  = $this->_checkGmbJson($gmb['locationAssociation']['priceListItemId']); 
        $mediaItem->gmb_google_url  = $this->_checkGmbJson($gmb['googleUrl']); 
        $mediaItem->gmb_thumbnail_url  = $this->_checkGmbJson($gmb['thumbnailUrl']); 
        $mediaItem->gmb_create_time  = $this->_checkGmbJson($gmb['createTime']); 
        $mediaItem->gmb_dimentions_width_pixels  = $this->_checkGmbJson($gmb['dimensions']['widthPixels'], 0); 
        $mediaItem->gmb_dimentions_height_pixels  = $this->_checkGmbJson($gmb['dimensions']['heightPixels'], 0); 
        $mediaItem->gmb_insights_view_count  = $this->_checkGmbJson($gmb['insights']['viewCount'], 0); 

        $mediaItem->gmb_attribution_profile_name  = $this->_checkGmbJson($gmb['attribution']['profileName']); 
        $mediaItem->gmb_attribution_profile_photo_url  = $this->_checkGmbJson($gmb['attribution']['profilePhotoUrl']); 
        $mediaItem->gmb_attribution_takedown_url  = $this->_checkGmbJson($gmb['attribution']['takedownUrl']); 
        $mediaItem->gmb_atttribution_profile_url  = $this->_checkGmbJson($gmb['attribution']['profileUrl']); 

        $mediaItem->gmb_description  = $this->_checkGmbJson($gmb['description']); 
        $mediaItem->gmb_source_url  = $this->_checkGmbJson($gmb['sourceUrl']); 
        $mediaItem->gmb_data_ref_resource_name  = $this->_checkGmbJson($gmb['dataRef']['resourceName']); 
        $mediaItem->s3_object_url  = $this->_checkGmbJson($gmb['']); 

        $mediaItem->is_deleted  = 0;
        $mediaItem->sync_status  = config('const.SYNC_STATUS.SYNCED');
        $mediaItem->create_user_id  = 0;

        $mediaItem->save();
    }


  }