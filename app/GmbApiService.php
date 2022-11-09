<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\Location;
use App\LocalPost;
use App\MediaItem;
use App\Review;
use App\ReviewReply;

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
        // for debug
        DB::enableQueryLog();
    }
    public function newGoogleClient($authFile)
    {
        $client = new Google_Client();
        $client->setApplicationName("マイビジチェーン");
        $client->setScopes(["https://www.googleapis.com/auth/plus.business.manage"]);
        $client->setAuthConfig($authFile);
        $client->setAccessType("offline");
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

            // 認証コードをトークンに交換
            //$client->setRedirectUri("urn:ietf:wg:oauth:2.0:oob");
            //$authUrl = $client->createAuthUrl();   
            //echo "authUrl=".$authUrl."\n\n"; <-- このURLをブラウザで表示して、高松さんのGoogleアカウントでログインして認証すると、リダイレクトされずに認証コードが表示されるので、その認証コードを使う

            //$code = "4/yQGgt-NlF9tiiQhun2u0RHUw2tYoKR-z6NlgKABHAxXf-UcwTG3gXX8";
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

    // 日付型データを文字列に変換する
    private function _convDatetime2String($datetime) {

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


    //全てのアカウント情報
    public function allAccounts($gmbService) {
 
        $gmb = $gmbService->accounts->listAccounts();
        if (isset($gmb['nextPageToken'])) {
            foreach($gmb['accounts'] as $account){
                $key = str_replace('accounts/', '', $account['name']);
                $this->getAccount($gmbService, $key);
            }            

            while (isset($gmb['nextPageToken'])) {
                $optParams = array('pageToken' => $gmb['nextPageToken']);
                $gmb = $gmbService->accounts->listAccounts($optParams);
                if ($gmb)  {
                  foreach($gmb['accounts'] as $account){
                    $key = str_replace('accounts/', '', $account['name']);  
                    $this->getAccount($gmbService, $key);
                  }
                }
            }
        }
    }

    //特定のアカウント情報
    public function getAccount($gmbService, $key) {
        
        $name = 'accounts/'.$key;
        $gmb = $gmbService->accounts->get($name);
        if ($gmb) {

            $account = Account::active()->where('gmb_account_id', '=', $key)->first();
            //dd(DB::getQueryLog());
            if ($account == NULL) {
                $this->_debug('新規作成 ' .$key);
                $account = new Account;
                $this->_syncAccount($account, $gmb);

            } else {
                $this->_debug('更新 ' .$key);
                if ($account->sync_status == config('const.SYNC_STATUS.SYNCED') || $account->sync_status == config('const.SYNC_STATUS.FAILED')) {
                    $this->_syncAccount($account, $gmb);
                }
            }
        }
    }

    // アカウント情報の同期
    private function _syncAccount($account, $gmb)
    {

        try {

            $account->gmb_account_id  = str_replace('accounts/', '', $gmb['name']);
            $account->gmb_account_name  = $gmb['accountName'];
            $account->gmb_account_type  = $gmb['type'];
            $account->gmb_account_role  = $gmb['role'];
            $account->gmb_account_state  = $gmb['state']['status'];
            $account->gmb_profile_photo_url  = $this->_checkGmbJson($gmb['profilePhotoUrl']);
            $account->gmb_account_number  = $this->_checkGmbJson($gmb['accountNumber']);
            $account->gmb_permission_level  = $gmb['permissionLevel'];
            // 組織アカウントのみ
            $account->gmb_orginfo_registered_domain  = $this->_checkGmbJson($gmb['organizationInfo']['registeredDomain']);
            $account->gmb_orginfo_postaladdr_region_code  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['regionCode']);
            $account->gmb_orginfo_postaladdr_language_code  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['languageCode']);
            $account->gmb_orginfo_postaladdr_postal_code  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['postalCode']);
            $account->gmb_orginfo_postaladdr_sorting_code  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['sortingCode']);
            $account->gmb_orginfo_postaladdr_admin_area  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['administrativeArea']);
            $account->gmb_orginfo_postaladdr_locality  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['locality']);
            $account->gmb_orginfo_postaladdr_sublocality  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['sublocality']);
            $account->gmb_orginfo_postaladdr_address_lines  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['addressLines']); 
            $account->gmb_orginfo_postaladdr_recipients  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['recipients']); 
            $account->gmb_orginfo_postaladdr_organization  = $this->_checkGmbJson($gmb['organizationInfo']['postalAddress']['organization']);
            $account->gmb_orginfo_phone_number  = $this->_checkGmbJson($gmb['organizationInfo']['phoneNumber']);

            $account->is_deleted  = 0;
            $account->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $account->create_user_id  = 0;

            $account->save();

        } catch ( Exception $e ) {
            // TODO
            logger()->error('_syncAccount Exception:' .$e->getMessage());
        }
    }

    // すべての店舗情報
    public function getLocations($gmbService, $gmbAccountId) {
     
        $account = Account::active()->where('gmb_account_id', '=', $gmbAccountId)->first();
        if ($account != NULL) {
            $accountId = $account->account_id;

            $name = 'accounts/'.$gmbAccountId;
            $gmb = $gmbService->accounts_locations->listAccountsLocations($name);
            if ($gmb) {
                foreach($gmb['locations'] as $location){
                    $this->_getLocation($accountId, $location);
                }
    
                do{

                    $this->_debug('nextPageToken=' .$gmb['nextPageToken']);

                    if (isset($gmb['nextPageToken'])) {
                            $optParams = array('pageToken' => $gmb['nextPageToken']);
                            $gmb = $gmbService->accounts_locations->listAccountsLocations($name, $optParams);
                            if ($gmb)  {
                                foreach($gmb['locations'] as $location){
                                    $this->_getLocation($accountId, $location);
                                }
                            } else {
                            break;
                        }
                    }
            
                } while(isset($gmb['nextPageToken']));
            }
        }
    }

    // 特定の店舗情報
    public function getLocation($gmbService, $gmbAccountId, $gmbLocationId)
    { 
        $account = Account::active()->where('gmb_account_id', '=', $gmbAccountId)->first();
        //dd(DB::getQueryLog());
       if ($account != NULL) {
            $accountId = $account->account_id;

            $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId;
            $gmb = $gmbService->accounts_locations->get($name);
            if ($gmb) {
                $this->_getLocation($accountId, $gmb);
            }
        }
    }

    // 店舗情報を取得
    private function _getLocation($accountId, $gmb)
    {
        // accounts/103433320649253515986/locations/6411054713785239288
        $nameAry = explode("/", $gmb['name']);
        $location = Location::active()
                            ->where('gmb_account_id', '=', $nameAry[1])
                            ->where('gmb_location_id', '=', $nameAry[3])
                            ->first();

        //dd(DB::getQueryLog());
        if ($location == NULL) {
            //$this->_debug('新規作成 ' .$gmb['name']);
            $location = new Location;
            $this->_syncLocation($accountId, $location, $gmb);

        } else {
            //$this->_debug('更新 ' .$gmb['name']);
            if ($location->sync_status == config('const.SYNC_STATUS.SYNCED') || $location->sync_status == config('const.SYNC_STATUS.FAILED')) {
                $this->_syncLocation($accountId, $location, $gmb);
            }
        }
    }

    // 店舗情報の同期
    private function _syncLocation($accountId, $location, $gmb)
    {
        $this->_debug('_syncLocation accountId=' .$accountId . ' '.$gmb['name'] .',' .$gmb['locationName']);
        $this->_debug($gmb['additionalPhones']);
        $this->_debug($gmb['storeCode']);
        $this->_debug($gmb['address']['languageCode']);

        try {

            $location->account_id  = $accountId;

            // accounts/103433320649253515986/locations/6411054713785239288
            $nameAry = explode("/", $gmb['name']);
            $location->gmb_account_id  = $nameAry[1];
            $location->gmb_location_id  = $nameAry[3];

            $location->gmb_language_code  = $this->_checkGmbJson($gmb['languageCode']);
            $location->gmb_store_code  = $this->_checkGmbJson($gmb['storeCode']);
            $location->gmb_location_name  = $this->_checkGmbJson($gmb['locationName']);
            $location->gmb_primary_phone  = $this->_checkGmbJson($gmb['primaryPhone']);

            if ($gmb['additionalPhones'] != NULL && $gmb['additionalPhones'] != 'NULL') // TODO
            {
                $location->gmb_additional_phones_1  = $this->_checkGmbJson(@$gmb['additionalPhones'][0]);
                $location->gmb_additional_phones_2  = $this->_checkGmbJson(@$gmb['additionalPhones'][1]);
            }

            $location->gmb_postaladdr_region_code  = $this->_checkGmbJson($gmb['address']['regionCode']);
            $location->gmb_postaladdr_language_code  = $this->_checkGmbJson($gmb['address']['languageCode']);
            $location->gmb_postaladdr_postal_code  = $this->_checkGmbJson($gmb['address']['postalCode']);
            $location->gmb_postaladdr_sorting_code  = $this->_checkGmbJson($gmb['address']['sortingCode']);
            $location->gmb_postaladdr_admin_area  = $this->_checkGmbJson($gmb['address']['administrativeArea']);
            $location->gmb_postaladdr_locality  = $this->_checkGmbJson($gmb['address']['locality']);
            $location->gmb_postaladdr_sublocality  = $this->_checkGmbJson($gmb['address']['sublocality']);
            $location->gmb_postaladdr_address_lines  = $this->_checkGmbJson($gmb['address']['addressLines']);
            $location->gmb_postaladdr_recipients  = $this->_checkGmbJson($gmb['address']['recipients']);
            $location->gmb_postaladdr_organization  = $this->_checkGmbJson($gmb['address']['organization']);

            // Object of class Google_Service_MyBusiness_Category could not be converted to string  
         //   $location->gmb_primary_category_id  = $this->_checkGmbJson($gmb['primaryCategory']); // TODO

            $location->gmb_website_url  = $this->_checkGmbJson($gmb['websiteUrl']);
            $location->gmb_servicearea_business_type  = $this->_checkGmbJson($gmb['serviceArea']['businessType'], 'CUSTOMER_AND_BUSINESS_LOCATION');

            // SQLSTATE[01000]: Warning: 1265 Data truncated for column 'gmb_servicearea_latitude' at row 1 
            //$location->gmb_servicearea_latitude  = $this->_checkGmbJson($gmb['serviceArea']['radius']['latlng']['latitude']);   // TODO
            //$location->gmb_servicearea_longitude  = $this->_checkGmbJson($gmb['serviceArea']['radius']['latlng']['longitude']); // TODO
            //$location->gmb_servicearea_radius_km  = $this->_checkGmbJson($gmb['serviceArea']['radius']['radiusKm']);            // TODO
            
            //class Google_Service_MyBusiness_PlaceInfo could not be converted to string  
            //$location->gmb_servicearea_placeinfo  = $this->_checkGmbJson($gmb['serviceArea']['places']['placeInfos']);          // TODO

            $location->gmb_locationkey_pluspage_id  = $this->_checkGmbJson($gmb['locationKey']['plusPageId']);
            $location->gmb_locationkey_place_id  = $this->_checkGmbJson($gmb['locationKey']['placeId']);
            $location->gmb_locationkey_explicit_no_place_id  = $this->_checkGmbJson($gmb['locationKey']['explicitNoPlaceId'],0);
            $location->gmb_locationkey_request_id  = $this->_checkGmbJson($gmb['locationKey']['requestId']);

            $location->gmb_labels  = $this->_checkGmbJson($gmb['labels']);
            $location->gmb_adwords_adphone  = $this->_checkGmbJson($gmb['adWordsLocationExtensions']['adPhone']);
            $location->gmb_latlng_latitude  = $this->_checkGmbJson($gmb['latlng']['latitude'], 0);
            $location->gmb_latlng_longitude  = $this->_checkGmbJson($gmb['latlng']['longitude'], 0);

            $location->gmb_openinfo_status  = $this->_checkGmbJson($gmb['openInfo']['status'], 'OPEN');
            $location->gmb_openinfo_can_reopen  = $this->_checkGmbJson($gmb['openInfo']['canReopen'], 0);
            $location->gmb_openinfo_opening_date  = $this->_checkGmbJson($gmb['openInfo']['openingDate']['year']);  // TODO  year + month + dayを編集

            $location->gmb_state_is_google_updated  = $this->_checkGmbJson($gmb['locationState']['isGoogleUpdated'], 0);   // TODO
            $location->gmb_state_is_duplicate  = $this->_checkGmbJson($gmb['locationState']['isDuplicate'], 0);
            $location->gmb_state_is_suspended  = $this->_checkGmbJson($gmb['locationState']['isSuspended'], 0);
            $location->gmb_state_can_update  = $this->_checkGmbJson($gmb['locationState']['canUpdate'], 0);
            $location->gmb_state_can_delete  = $this->_checkGmbJson($gmb['locationState']['canDelete'], 0);
            $location->gmb_state_is_verified  = $this->_checkGmbJson($gmb['locationState']['isVerified'], 0);
            $location->gmb_state_needs_reverification  = $this->_checkGmbJson($gmb['locationState']['needsReverification'], 0);
            $location->gmb_state_is_pending_review  = $this->_checkGmbJson($gmb['locationState']['isPendingReview'], 0);
            $location->gmb_state_is_disabled  = $this->_checkGmbJson($gmb['locationState']['isDisabled'], 0);
            $location->gmb_state_is_published  = $this->_checkGmbJson($gmb['locationState']['isPublished'], 0);
            $location->gmb_state_is_disconnected  = $this->_checkGmbJson($gmb['locationState']['isDisconnected'], 0);
            $location->gmb_state_is_local_post_api_disabled  = $this->_checkGmbJson($gmb['locationState']['isLocalPostApiDisabled'], 0);
            $location->gmb_state_has_pending_edits  = $this->_checkGmbJson($gmb['locationState']['hasPendingEdits'], 0);
            $location->gmb_state_has_pending_verification  = $this->_checkGmbJson($gmb['locationState']['hasPendingVerification'], 0);

            $location->gmb_metadata_duplicate_location_name  = $this->_checkGmbJson($gmb['metadata']['duplicate']['locationName']);     // TODO
            $location->gmb_metadata_duplicate_place_id  = $this->_checkGmbJson($gmb['metadata']['duplicate']['placeId']);
            $location->gmb_metadata_duplicate_access  = $this->_checkGmbJson($gmb['metadata']['duplicate']['access'],'ACCESS_UNSPECIFIED');
            $location->gmb_metadata_maps_url  = $this->_checkGmbJson($gmb['metadata']['mapsUrl']);
            $location->gmb_metadata_new_review_url  = $this->_checkGmbJson($gmb['metadata']['newReviewUrl']);

            $location->gmb_profile_description  = $this->_checkGmbJson($gmb['profile']['description']);
            $location->gmb_relationship_parent_chain  = $this->_checkGmbJson($gmb['relationshipData']['parentChain']);

            $location->is_deleted  = 0;
            $location->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $location->create_user_id  = 0;

            $location->save();

        } catch ( Exception $e ) {
            // TODO
            logger()->error('_syncLocation Exception: ' . $gmb['name'] .'  ' .$e->getMessage());
        }
    }
    
    // 店舗のすべての投稿情報
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

    // 特定の投稿情報
    public function getLocalPost($gmbService, $gmbAccountId, $gmbLocationId, $gmbLocalPostId)
    { 
        $location = Location::active()
                            ->where('gmb_account_id', '=', $gmbAccountId)
                            ->where('gmb_location_id', '=', $gmbLocationId)
                            ->first();
        //dd(DB::getQueryLog());
        if ($location != NULL) {
            $accountId = $location->account_id;
            $locationId = $location->account_id;
            
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


    // 店舗のすべてのクチコミを取得
    public function getReviews($gmbService, $gmbAccountId, $gmbLocationId)
    { 
        //DB::enableQueryLog();

        $location = Location::active()
                            ->where('gmb_account_id', '=', $gmbAccountId)
                            ->where('gmb_location_id', '=', $gmbLocationId)
                            ->first();

        //logger()->error(DB::getQueryLog());

        if ($location != NULL) {
            $accountId = $location->account_id;
            $locationId = $location->location_id;
            $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId;

            $optParams = array('pageSize' => 100);
            $gmb = $gmbService->accounts_locations_reviews->listAccountsLocationsReviews($name, $optParams);
            if ($gmb) {

                foreach($gmb['reviews'] as $review){
                    $this->_debug('displayName=' .$review["reviewer"]["displayName"]);
                    $this->_getReview($accountId, $locationId, $review);
                    /*
                    ob_start();
                    echo "review ここから 1---------------------";
                    var_dump($review);
                    $out = ob_get_contents();
                    ob_end_clean();
                    logger()->error($out);
                    */
                }
    
                do{
                    if (isset($gmb['nextPageToken'])) {
                        $optParams = array('pageSize' => 100, 'pageToken' => $gmb['nextPageToken']);
                        $gmb = $gmbService->accounts_locations_reviews->listAccountsLocationsReviews($name, $optParams);
                        if ($gmb)  {
                            foreach($gmb['reviews'] as $review){
                                $this->_getReview($accountId, $locationId, $review);
                            }
                        } else {
                            break;
                        }
                    }

                } while(isset($gmb['nextPageToken']));
            }

        }

    }

    // 特定のクチコミを取得
    public function getReview($gmbService, $gmbAccountId, $gmbLocationId, $gmbReviewId)
    { 
        DB::enableQueryLog();

        $review = Review::active()
                            ->where('gmb_account_id', '=', $gmbAccountId)
                            ->where('gmb_location_id', '=', $gmbLocationId)
                            ->where('gmb_review_id', '=', $gmbReviewId)
                            ->first();
       
        logger()->error(DB::getQueryLog());

        
        $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId .'/reviews/' .$gmbReviewId;
        $gmb = $gmbService->accounts_locations_reviews->get($name);
        if ($gmb) {
            if ($review == NULL) {
                $this->_syncReview($locationId, $gmb);
            } else {
                $locationId = $review->location_id;
                $this->_getReview($locationId, $gmb);
            }
        }
    }

    private function _syncReview($locationId, $gmb)
    {

        ob_start();
        echo "_syncReview ---------------------";
        var_dump($gmb);
        $out = ob_get_contents();
        ob_end_clean();
        logger()->error($out);


    }



    // 投稿情報を取得
    private function _getReviewReply($accountId, $locationId, $gmb)
    {
        logger()->error($gmb);
        DB::enableQueryLog();

        // accounts/103433320649253515986/locations/6411054713785239288/reviews/xxxxxxxxxxxxxxx
        $nameAry = explode("/", $gmb['name']);
        $reviewReply = ReviewReply::active()
                            ->where('gmb_account_id', '=', $nameAry[1])
                            ->where('gmb_location_id', '=', $nameAry[3])
                            ->where('gmb_local_post_id', '=', $nameAry[5])
                            ->first();

        logger()->error(DB::getQueryLog());

        if ($reviewReply == NULL) {
            //$this->_debug('新規作成 ' .$gmb['name']);
            $reviewReply = new ReviewReply;
          //  $this->_syncReviewReply($locationId, 0, $localPost, $gmb);

        } else {
            //$this->_debug('更新 ' .$gmb['name']);
            if ($reviewReply->sync_status == config('const.SYNC_STATUS.SYNCED') || $reviewReply->sync_status == config('const.SYNC_STATUS.FAILED')) {
          //      $this->_syncReviewReply($locationId, $localPost->local_post_id, $localPost, $gmb);
            }
        }
    }
    

    private function _syncReviewReply($localPostId, $mediaItem, $gmb)
    {

        //ReviewReply


    }




    private function _checkGmbJson($value, $default='') {

        try {
            if ($value == NULL || $value == 'NULL') $value = $default;
            else {
                if (is_array($value)) {
                    $value = implode(",", str_replace(',', '、', $value));
                }
            }

        } catch ( Exception $e ) {   
            // TODO
            $value = $default;
        }

        return $value;
    }

    private function _checkGmbJsonScheduleTime($date, $time) {

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

    private function _debug($msg) {
        var_dump($msg);
    }
  }