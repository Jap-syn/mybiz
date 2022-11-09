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
use Google_Service_MyBusiness_LocalPostProduct;
use Google_Service_MyBusiness_MediaItem;
use Google_Service_MyBusiness_LocationAssociation;
use Google_Service_MyBusiness_Review;
use Google_Service_MyBusiness_ReviewReply;
use Google_Service_Exception;
use Carbon\Carbon;

class GmbApiLocalpostQueryService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_account_count;
    private $_started_at;
    private $_ended_at;

    public function __construct()
    {
        // for debug
     //   DB::enableQueryLog();
    }

    // 投稿を取得
    public function getLocalPosts($gmbService, $gmbApiService, $gmbAccountId=null, $gmbLocationId=null)
    { 

        $this->_kubun = 1;
        $this->_class_function = "GmbApiLocalpostQueryService.getLocalPosts";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_localpost_new_count = 0;
        $this->_localpost_update_count = 0;
        $this->_mediaitem_new_count = 0;
        $this->_mediaitem_update_count = 0;

        if ($gmbAccountId == null && $gmbLocationId == null) {
            // 契約企業全てのブランド・店舗の投稿を取得
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
                    $this->_getLocalPosts($gmbService, $gmbApiService, $account['gmb_account_id'], $location['gmb_location_id']);
                    unset($location);
                }
                unset($locations);
            }
            unset($accounts);

        } else if ($gmbLocationId == null) {
            // 指定されたブランド配下の全店舗の投稿を取得
            $locations = Location::select(['gmb_location_id'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)
                                ->get();

            foreach ($locations as $location) {
                $this->_getLocalPosts($gmbService, $gmbApiService, $gmbAccountId, $location['gmb_location_id']);
                unset($location);
            }
            unset($locations);

        } else {
            // 指定された店舗の投稿を取得
            $this->_getLocalPosts($gmbService, $gmbApiService, $gmbAccountId, $gmbLocationId);
        }

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("localpost_new_count=%d, localpost_update_count=%d, mediaitem_new_count=%d, mediaitem_update_count=%d", 
                                $this->_localpost_new_count, $this->_localpost_update_count, $this->_mediaitem_new_count, $this->_mediaitem_update_count);
        $this->_logging($gmbApiService);

     //   logger()->error(DB::getQueryLog());
    }

    // 店舗の投稿を取得
    private function _getLocalPosts($gmbService, $gmbApiService, $gmbAccountId, $gmbLocationId)
    { 

        try {

            $location = Location::select(['account_id', 'location_id'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)
                                ->where('gmb_location_id', '=', $gmbLocationId)
                                ->first();

            if ($location != null) {
                $accountId = $location->account_id;
                $locationId = $location->location_id;

                $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId;
                $gmb = $gmbService->accounts_locations_localPosts->listAccountsLocationsLocalPosts($name);
                if ($gmb) {
                    foreach($gmb['localPosts'] as $localPost){
                        $this->_syncLocalPost($gmbService, $gmbApiService, $accountId, $locationId, $localPost);
                        unset($localPost);
                    }
        
                    do{
                        if (isset($gmb['nextPageToken'])) {
                            $optParams = array('pageToken' => $gmb['nextPageToken']);
                            $gmb = $gmbService->accounts_locations->listAccountsLocations($name, $optParams);
                            if ($gmb)  {
                                foreach($gmb['localPosts'] as $localPost){
                                    $this->_syncLocalPost($gmbService, $gmbApiService, $accountId, $locationId, $localPost);
                                    unset($localPost);
                                }
                            } else {
                                unset($gmb);
                                break;
                            }
                        }

                    } while(isset($gmb['nextPageToken']));
                }
                unset($gmb);
            }

        }catch(Google_Service_Exception $e){
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiLocalpostQueryService._getLocalPosts";
            $this->_detail = sprintf("name=%s", 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            // 開放
            $location = null;
        }
    }

    // 投稿情報を取得
    private function _syncLocalPost($gmbService, $gmbApiService, $accountId, $locationId, $gmb)
    {
        $nameAry = explode("/", $gmb['name']);
        $localPost = LocalPost::active()
                            ->where('gmb_account_id', '=', $nameAry[1])
                            ->where('gmb_location_id', '=', $nameAry[3])
                            ->where('gmb_local_post_id', '=', $nameAry[5])
                            ->first();
        if ($localPost == NULL) {
            #$this->_debug('LocalPost 新規作成 ' .$gmb['name']);
            $localPost = new LocalPost;
            $this->_saveLocalPost($gmbService, $gmbApiService, $locationId, 0, $localPost, $gmb);
            $this->_localpost_new_count ++;

        } else {
            #$this->_debug('LocalPost 更新 ' .$gmb['name']);
            if ($localPost->sync_status == config('const.SYNC_STATUS.SYNCED')) {
                $this->_saveLocalPost($gmbService, $gmbApiService, $locationId, $localPost->local_post_id, $localPost, $gmb);
                $this->_localpost_update_count ++;
            }
        }
    }

    // 投稿情報の同期
    private function _saveLocalPost($gmbService, $gmbApiService, $locationId, $localPostId, $localPost, $gmb)
    {

        try {

            DB::beginTransaction();

            $localPost->location_id  = $locationId;

            $nameAry = explode("/", $gmb['name']);
            $localPost->gmb_account_id  = $nameAry[1];
            $localPost->gmb_location_id  = $nameAry[3];
            $localPost->gmb_local_post_id  = $nameAry[5];

            $localPost->gmb_language_code  = $gmbApiService->checkGmbJson($gmb['languageCode']);
            $localPost->gmb_summary  = $gmbApiService->checkGmbJson($gmb['summary']);
            $localPost->gmb_action_type  = $gmbApiService->checkGmbJson($gmb['callToAction']['actionType'], 'LEARN_MORE');
            $localPost->gmb_action_type_url  = $gmbApiService->checkGmbJson($gmb['callToAction']['url']);

            $localPost->gmb_create_time  = $gmbApiService->covertTimezone2Jst($gmb['createTime']); 
            $localPost->gmb_update_time  = $gmbApiService->covertTimezone2Jst($gmb['updateTime']);

            $localPost->gmb_event_title  = $gmbApiService->checkGmbJson($gmb['event']['title']);
            $localPost->gmb_event_start_time  = $gmbApiService->checkGmbJsonScheduleTime($gmb['event']['schedule']['startDate'],$gmb['event']['schedule']['startTime']); 
            $localPost->gmb_event_end_time  = $gmbApiService->checkGmbJsonScheduleTime($gmb['event']['schedule']['endDate'],$gmb['event']['schedule']['endTime']); 

            $localPost->gmb_local_post_state  = $gmbApiService->checkGmbJson($gmb['state'], 'LIVE');
            $localPost->gmb_search_url  = $gmbApiService->checkGmbJson($gmb['searchUrl']);
            $localPost->gmb_topic_type  = $gmbApiService->checkGmbJson($gmb['topicType'], 'EVENT');

            $localPost->gmb_offer_coupon_code  = $gmbApiService->checkGmbJson($gmb['offer']['couponCode']);
            $localPost->gmb_offer_redeem_online_url  = $gmbApiService->checkGmbJson($gmb['offer']['redeemOnlineUrl']);
            $localPost->gmb_offer_terms_conditions  = $gmbApiService->checkGmbJson($gmb['offer']['termsConditions']);

            #$localPost->is_deleted  = 0;
            $localPost->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $localPost->create_user_id  = 0;

            $localPost->save();

            if ($localPostId == 0) {
                $localPostId = $localPost->local_post_id;
            }

            /*
            MediaItem::active()
                        ->where('gmb_account_id', '=', $nameAry[1])
                        ->where('gmb_location_id', '=', $nameAry[3])
                        ->update([
                            'is_deleted' => config('const.FLG_ON')
                        ]);
            */
            // 投稿時の写真を同期
            foreach($gmb['media'] as $media){
                $media_nameAry = explode("/", $media['name']);
                $mediaItem = MediaItem::where('gmb_account_id', '=', $media_nameAry[1])
                                    ->where('gmb_location_id', '=', $media_nameAry[3])
                                    ->where('gmb_media_key', '=', $media_nameAry[5] .'/' .$media_nameAry[6])
                                    ->first();
             
                if ($mediaItem == NULL) {
                    #$this->_debug('MediaItem 新規作成 ' .$gmb['name']);
                    $mediaItem = new MediaItem;
                    $this->_saveMediaItem($gmbService, $gmbApiService, $localPostId, $mediaItem, $media);
                    $this->_mediaitem_new_count ++;

                } else {
                    #$this->_debug('MediaItem 更新 ' .$gmb['name']);
                    if ($mediaItem->sync_status == config('const.SYNC_STATUS.SYNCED')) {
                        $this->_saveMediaItem($gmbService, $gmbApiService, $localPostId, $mediaItem, $media);
                        $this->_mediaitem_update_count ++;
                    }
                }
                unset($media_nameAry);
                unset($media);
            }

            DB::commit();

        } catch ( QueryException $e ) {
            DB::rollBack();

            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiLocalpostQueryService._saveLocalPost";
            $this->_detail = sprintf("name=%s", $gmb['name']);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $localPost = null;
            $mediaItem = null;
        }
    }

    // 投稿時の写真を同期
    private function _saveMediaItem($gmbService, $gmbApiService, $localPostId, $mediaItem, $gmb)
    {

        $mediaItem->local_post_id  = $localPostId;

        // accounts/103433320649253515986/locations/6411054713785239288/media/localPosts/AF1QipNhNgZw_hWjrTpx17oOTZrXaozkmZbQsHcLFHQ"
        $nameAry = explode("/", $gmb['name']);
        $mediaItem->gmb_account_id  = $nameAry[1];
        $mediaItem->gmb_location_id  = $nameAry[3]; 
        $mediaItem->gmb_media_key  = $nameAry[5] .'/' .$nameAry[6]; 

        $mediaItem->gmb_media_format  = $gmbApiService->checkGmbJson($gmb['mediaFormat'], 'MEDIA_FORMAT_UNSPECIFIED'); 
        $mediaItem->gmb_location_association_category  = $gmbApiService->checkGmbJson($gmb['locationAssociation']['category'], 'CATEGORY_UNSPECIFIED'); 
        $mediaItem->gmb_location_association_price_list_item_id  = $gmbApiService->checkGmbJson($gmb['locationAssociation']['priceListItemId']); 
        $mediaItem->gmb_google_url  = $gmbApiService->checkGmbJson($gmb['googleUrl']); 
        $mediaItem->gmb_thumbnail_url  = $gmbApiService->checkGmbJson($gmb['thumbnailUrl']); 
        $mediaItem->gmb_create_time  = $gmbApiService->covertTimezone2Jst($gmb['createTime']); 
        $mediaItem->gmb_dimentions_width_pixels  = $gmbApiService->checkGmbJson($gmb['dimensions']['widthPixels'], 0); 
        $mediaItem->gmb_dimentions_height_pixels  = $gmbApiService->checkGmbJson($gmb['dimensions']['heightPixels'], 0); 
        $mediaItem->gmb_insights_view_count  = $gmbApiService->checkGmbJson($gmb['insights']['viewCount'], 0); 

        $mediaItem->gmb_attribution_profile_name  = $gmbApiService->checkGmbJson($gmb['attribution']['profileName']); 
        $mediaItem->gmb_attribution_profile_photo_url  = $gmbApiService->checkGmbJson($gmb['attribution']['profilePhotoUrl']); 
        $mediaItem->gmb_attribution_takedown_url  = $gmbApiService->checkGmbJson($gmb['attribution']['takedownUrl']); 
        $mediaItem->gmb_atttribution_profile_url  = $gmbApiService->checkGmbJson($gmb['attribution']['profileUrl']); 

        $mediaItem->gmb_description  = $gmbApiService->checkGmbJson($gmb['description']); 
        $mediaItem->gmb_source_url  = $gmbApiService->checkGmbJson($gmb['sourceUrl']); 
        $mediaItem->gmb_data_ref_resource_name  = $gmbApiService->checkGmbJson($gmb['dataRef']['resourceName']); 

        #$mediaItem->is_deleted  = 0;
        $mediaItem->sync_status  = config('const.SYNC_STATUS.SYNCED');
        $mediaItem->create_user_id  = 0;

        $mediaItem->save();
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