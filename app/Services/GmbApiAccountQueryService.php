<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
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

class GmbApiAccountQueryService
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

    //全てのアカウント情報
    public function getAccounts($gmbService, $gmbApiService) {
 
        $this->_kubun = 1;
        $this->_class_function = "GmbApiAccountQueryService.getAccounts";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_account_count = 0;

        $gmb = $gmbService->accounts->listAccounts();
        if (isset($gmb['nextPageToken'])) {
            foreach($gmb['accounts'] as $account){
                $gmbAccountId = str_replace('accounts/', '', $account['name']);
                $this->getAccount($gmbService, $gmbApiService, $gmbAccountId);
                $this->_account_count++;
                unset($account);
            }

            while (isset($gmb['nextPageToken'])) {
                $optParams = array('pageToken' => $gmb['nextPageToken']);
                $gmb = $gmbService->accounts->listAccounts($optParams);
                if ($gmb)  {
                  foreach($gmb['accounts'] as $account){
                    $gmbAccountId = str_replace('accounts/', '', $account['name']);  
                    $this->getAccount($gmbService, $gmbApiService, $gmbAccountId);
                    $this->_account_count++;
                    unset($account);
                  }
                }
            }
        }
        unset($gmb);

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("account_count=%d", $this->_account_count);
        $this->_logging($gmbApiService);

     //   logger()->error(DB::getQueryLog());
    }

    //特定のアカウント情報
    public function getAccount($gmbService, $gmbApiService, $gmbAccountId) {
        $name = 'accounts/'.$gmbAccountId;
        $gmb = $gmbService->accounts->get($name);
        if ($gmb) {
            $account = Account::select(['account_id','sync_status'])
                                ->active()
                                ->where('gmb_account_id', '=', $gmbAccountId)->first();
            if ($account == null) {
                $account = new Account;
                $this->_syncAccount($gmbApiService, $account, $gmb);

            } else {
                if ($account->sync_status == config('const.SYNC_STATUS.SYNCED')) {
                    $this->_syncAccount($gmbApiService, $account, $gmb);
                }
            }
            $account = null;
        }
        unset($gmb);
    }

    // アカウント情報の同期
    private function _syncAccount($gmbApiService, $account, $gmb)
    {

        try {

            $account->gmb_account_id  = str_replace('accounts/', '', $gmb['name']);
            $account->gmb_account_name  = $gmb['accountName'];
            $account->gmb_account_type  = $gmb['type'];
            $account->gmb_account_role  = $gmb['role'];
            $account->gmb_account_state  = $gmb['state']['status'];
            $account->gmb_profile_photo_url  = $gmbApiService->checkGmbJson($gmb['profilePhotoUrl']);
            $account->gmb_account_number  = $gmbApiService->checkGmbJson($gmb['accountNumber']);
            $account->gmb_permission_level  = $gmb['permissionLevel'];
            // 組織アカウントのみ
            $account->gmb_orginfo_registered_domain  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['registeredDomain']);
            $account->gmb_orginfo_postaladdr_region_code  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['regionCode']);
            $account->gmb_orginfo_postaladdr_language_code  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['languageCode']);
            $account->gmb_orginfo_postaladdr_postal_code  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['postalCode']);
            $account->gmb_orginfo_postaladdr_sorting_code  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['sortingCode']);
            $account->gmb_orginfo_postaladdr_admin_area  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['administrativeArea']);
            $account->gmb_orginfo_postaladdr_locality  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['locality']);
            $account->gmb_orginfo_postaladdr_sublocality  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['sublocality']);
            $account->gmb_orginfo_postaladdr_address_lines  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['addressLines']); 
            $account->gmb_orginfo_postaladdr_recipients  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['recipients']); 
            $account->gmb_orginfo_postaladdr_organization  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['postalAddress']['organization']);
            $account->gmb_orginfo_phone_number  = $gmbApiService->checkGmbJson($gmb['organizationInfo']['phoneNumber']);

            $account->is_deleted  = 0;
            $account->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $account->sync_time  = Carbon::now();
            $account->create_user_id  = 0;

            $account->save();

        } catch ( QueryException $e ) {
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiAccountQueryService._syncAccount";
            $this->_detail = sprintf("name=%s", $gmb['name']);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $account = null;
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