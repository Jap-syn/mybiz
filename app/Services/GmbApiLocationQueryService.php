<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\Location;
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

class GmbApiLocationQueryService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_location_count;
    private $_started_at;
    private $_ended_at;

    public function __construct()
    {
        // for debug
     //   DB::enableQueryLog();
    }

    // すべてのアカウントの全店舗情報
    public function getLocationsForAllAccounts($gmbService, $gmbApiService) {

        $this->_kubun = 1;
        $this->_class_function = "GmbApiLocationQueryService.getLocationsForAllAccounts";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_location_count = 0;

        $accounts = Account::active()->get();
        foreach ($accounts as $account) {
            $gmbAccountId = $account->gmb_account_id;
            $this->getLocations($gmbService, $gmbApiService, $gmbAccountId);
            unset($account);
        }

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("location_count=%d", $this->_location_count);
        $this->_logging($gmbApiService);

        $accounts = null;
    }

    // 特定のアカウントの全店舗情報
    public function getLocations($gmbService, $gmbApiService, $gmbAccountId) {
     
        try {

            $account = Account::select(['account_id'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)->first();

            if ($account != null) {
                $accountId = $account->account_id;

                $name = 'accounts/'.$gmbAccountId;
                $gmb = $gmbService->accounts_locations->listAccountsLocations($name);
                if ($gmb) {
                    foreach($gmb['locations'] as $location){
                        $this->_getLocation($gmbApiService, $accountId, $location);
                        $this->_location_count++;
                        unset($location);
                    }
        
                    do{
                        if (isset($gmb['nextPageToken'])) {
                            $optParams = array('pageToken' => $gmb['nextPageToken']);
                            $gmb = $gmbService->accounts_locations->listAccountsLocations($name, $optParams);
                            if ($gmb)  {
                                foreach($gmb['locations'] as $location){
                                    $this->_getLocation($gmbApiService, $accountId, $location);
                                    $this->_location_count++;
                                    unset($location);
                                }
                            //  unset($gmb);

                            } else {
                                break;
                            }
                        }
                
                    } while(isset($gmb['nextPageToken']));
                }
                unset($gmb);
            }

        } catch ( Google_Service_Exception $e ) {
            // ログ
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiLocationQueryService.getLocations";
            $this->_detail = sprintf("name=%s", $name);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $accounts = null;
        }

    }

    // 特定の店舗情報
    public function getLocation($gmbService, $gmbApiService, $gmbAccountId, $gmbLocationId)
    { 
        $account = Account::active()->where('gmb_account_id', '=', $gmbAccountId)->first();
        if ($account != null) {
            $name = 'accounts/'.$gmbAccountId .'/locations/' .$gmbLocationId;
            $gmb = $gmbService->accounts_locations->get($name);
            if ($gmb) {
                echo $this->_debug($gmb);
                $accountId = $account->account_id;
                $this->_getLocation($gmbApiService, $accountId, $gmb);
            }
            unset($gmb);
        }
        $account = null;
    }

    // 店舗情報を取得
    private function _getLocation($gmbApiService, $accountId, $gmb)
    {
        // accounts/103433320649253515986/locations/6411054713785239288
        $nameAry = explode("/", $gmb['name']);
        $location = Location::select(['account_id','location_id','sync_status'])
                            ->active()
                            ->where('gmb_account_id', '=', $nameAry[1])
                            ->where('gmb_location_id', '=', $nameAry[3])
                            ->first();

        if ($location == null) {
            $location = new Location;
            $this->_syncLocation($gmbApiService, $accountId, $location, $gmb);

        } else {
            if ($location->sync_status == config('const.SYNC_STATUS.SYNCED')) {
                $this->_syncLocation($gmbApiService, $accountId, $location, $gmb);
            }
        }

        unset($nameAry);
        $location = null;
    }

    // 店舗情報の同期
    private function _syncLocation($gmbApiService, $accountId, $location, $gmb)
    {

        try {

            //logger()->error(print_r($gmb, true));

            $location->account_id  = $accountId;

            $nameAry = explode("/", $gmb['name']);
            $location->gmb_account_id  = $nameAry[1];
            $location->gmb_location_id  = $nameAry[3];

            $location->gmb_language_code  = $gmbApiService->checkGmbJson($gmb['languageCode']);
            $location->gmb_store_code  = $gmbApiService->checkGmbJson($gmb['storeCode']);
            $location->gmb_location_name  = $gmbApiService->checkGmbJson($gmb['locationName']);
            $location->gmb_primary_phone  = $gmbApiService->checkGmbJson($gmb['primaryPhone']);

            if ($gmb['additionalPhones'] != NULL && $gmb['additionalPhones'] != 'NULL') // TODO
            {
                $location->gmb_additional_phones_1  = $gmbApiService->checkGmbJson(@$gmb['additionalPhones'][0]);
                $location->gmb_additional_phones_2  = $gmbApiService->checkGmbJson(@$gmb['additionalPhones'][1]);
            }

            $location->gmb_postaladdr_region_code  = $gmbApiService->checkGmbJson($gmb['address']['regionCode']);
            $location->gmb_postaladdr_language_code  = $gmbApiService->checkGmbJson($gmb['address']['languageCode']);
            $location->gmb_postaladdr_postal_code  = $gmbApiService->checkGmbJson($gmb['address']['postalCode']);
            $location->gmb_postaladdr_sorting_code  = $gmbApiService->checkGmbJson($gmb['address']['sortingCode']);
            $location->gmb_postaladdr_admin_area  = $gmbApiService->checkGmbJson($gmb['address']['administrativeArea']);
            $location->gmb_postaladdr_locality  = $gmbApiService->checkGmbJson($gmb['address']['locality']);
            $location->gmb_postaladdr_sublocality  = $gmbApiService->checkGmbJson($gmb['address']['sublocality']);
            $location->gmb_postaladdr_address_lines  = $gmbApiService->checkGmbJson($gmb['address']['addressLines']);
            $location->gmb_postaladdr_recipients  = $gmbApiService->checkGmbJson($gmb['address']['recipients']);
            $location->gmb_postaladdr_organization  = $gmbApiService->checkGmbJson($gmb['address']['organization']);

            $location->gmb_website_url  = $gmbApiService->checkGmbJson($gmb['websiteUrl']);

            //   $location->gmb_primary_category_id  = $gmbApiService->checkGmbJson($gmb['primaryCategory']['categoryId']); // TODO JSON構造変更対応

            /*
            if (isset($gmb['serviceArea'])) {
                $location->gmb_servicearea_business_type  = $gmbApiService->checkGmbJson($gmb['serviceArea']['businessType'], 'CUSTOMER_AND_BUSINESS_LOCATION');
                $location->gmb_servicearea_latitude  = $gmbApiService->checkGmbJson($gmb['serviceArea']['radius']['latlng']['latitude']);   
                $location->gmb_servicearea_longitude  = $gmbApiService->checkGmbJson($gmb['serviceArea']['radius']['latlng']['longitude']); 
                $location->gmb_servicearea_radius_km  = $gmbApiService->checkGmbJson($gmb['serviceArea']['radius']['radiusKm']); 
                
                $placeInfos_name = $gmbApiService->checkGmbJson($gmb['serviceArea']['places']['placeInfos']['name']);
                $placeInfos_placeId = $gmbApiService->checkGmbJson($gmb['serviceArea']['places']['placeInfos']['placeId']);
                $location->gmb_servicearea_placeinfo  = $placeInfos_name ."," .$placeInfos_placeId;
            }
            */

            $location->gmb_locationkey_pluspage_id  = $gmbApiService->checkGmbJson($gmb['locationKey']['plusPageId']);
            $location->gmb_locationkey_place_id  = $gmbApiService->checkGmbJson($gmb['locationKey']['placeId']);
            $location->gmb_locationkey_explicit_no_place_id  = $gmbApiService->checkGmbJson($gmb['locationKey']['explicitNoPlaceId'],0);
            $location->gmb_locationkey_request_id  = $gmbApiService->checkGmbJson($gmb['locationKey']['requestId']);

            $location->gmb_labels  = $gmbApiService->checkGmbJson($gmb['labels']);
            $location->gmb_adwords_adphone  = $gmbApiService->checkGmbJson($gmb['adWordsLocationExtensions']['adPhone']);
            $location->gmb_latlng_latitude  = $gmbApiService->checkGmbJson($gmb['latlng']['latitude'], 0);
            $location->gmb_latlng_longitude  = $gmbApiService->checkGmbJson($gmb['latlng']['longitude'], 0);

            /*
            if (isset($gmb['openInfo'])) {
                $location->gmb_openinfo_status  = $gmbApiService->checkGmbJson($gmb['openInfo']['status'], 'OPEN');
                $location->gmb_openinfo_can_reopen  = $gmbApiService->checkGmbJson($gmb['openInfo']['canReopen'], 0);
                $location->gmb_openinfo_opening_date  = $gmbApiService->checkGmbJson($gmb['openInfo']['openingDate']['year']);  // TODO  year + month + dayを編集
            }
            */

            $location->gmb_state_is_google_updated  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isGoogleUpdated']); 
            $location->gmb_state_is_duplicate  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isDuplicate']);
            $location->gmb_state_is_suspended  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isSuspended']);
            $location->gmb_state_can_update  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['canUpdate']);
            $location->gmb_state_can_delete  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['canDelete']);
            $location->gmb_state_is_verified  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isVerified']);
            $location->gmb_state_needs_reverification  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['needsReverification']);
            $location->gmb_state_is_pending_review  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isPendingReview']);
            $location->gmb_state_is_disabled  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isDisabled']);
            $location->gmb_state_is_published  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isPublished']);
            $location->gmb_state_is_disconnected  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isDisconnected']);
            $location->gmb_state_is_local_post_api_disabled  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['isLocalPostApiDisabled']);
            $location->gmb_state_has_pending_edits  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['hasPendingEdits']);
            $location->gmb_state_has_pending_verification  = $gmbApiService->checkBooleanGmbJson($gmb['locationState']['hasPendingVerification']);

            $location->gmb_metadata_duplicate_location_name  = $gmbApiService->checkGmbJson($gmb['metadata']['duplicate']['locationName']);
            $location->gmb_metadata_duplicate_place_id  = $gmbApiService->checkGmbJson($gmb['metadata']['duplicate']['placeId']);
            $location->gmb_metadata_duplicate_access  = $gmbApiService->checkGmbJson($gmb['metadata']['duplicate']['access'],'ACCESS_UNSPECIFIED');
            $location->gmb_metadata_maps_url  = $gmbApiService->checkGmbJson($gmb['metadata']['mapsUrl']);
            $location->gmb_metadata_new_review_url  = $gmbApiService->checkGmbJson($gmb['metadata']['newReviewUrl']);

            $location->gmb_profile_description  = $gmbApiService->checkGmbJson($gmb['profile']['description']);
            $location->gmb_relationship_parent_chain  = $gmbApiService->checkGmbJson($gmb['relationshipData']['parentChain']);

            $location->is_deleted  = 0;
            $location->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $location->sync_time  = Carbon::now();
            $location->create_user_id  = 0;

            $location->save();

        } catch ( QueryException $e ) {
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiLocationQueryService._syncLocation";
            $this->_detail = sprintf("name=%s", $gmb['name']);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            unset($nameAry);
            $location = null;
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