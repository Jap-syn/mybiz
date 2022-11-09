<?php
// 修正
namespace App\Services;

use DB;
use App\Account;
use App\MediaItem2;
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

class GmbApiMediaItem2RegistService
{
    private $_proc_exit;
    private $_kubun;
    private $_class_function;
    private $_detail;
    private $_exception;
    private $_account_count;
    private $_started_at;
    private $_ended_at;
    private $_mediaitem_regist_count;
    private $_mediaitem_delete_count;
    private $_mediaitem_exception_count;
    
    public function __construct()
    {
    }

    // 写真の登録・変更・削除
    public function registMediaItems2($gmbService, $gmbApiService)
    { 
        $this->_kubun = 0;
        $this->_proc_exit = 0;
        $this->_class_function = "GmbApiMediaItemRegistService.registMediaItems2";
        $this->_detail = "";
        $this->_exception = "";
        $this->_started_at = Carbon::now();
        $this->_mediaitem_regist_count = 0;
        $this->_mediaitem_delete_count = 0;
        $this->_mediaitem_exception_count = 0;

        $dsMediaItem = MediaItem2::queued()
                                ->where('scheduled_sync_time', '<=', Carbon::now())
                                ->orderBy('gmb_account_id', 'asc')->orderBy('gmb_location_id', 'asc')->orderBy('gmb_location_id', 'asc')
                                ->get();

        foreach($dsMediaItem as $rsMediaItem){
            if ($rsMediaItem->sync_type == config('const.SYNC_TYPE.CREATE') || $rsMediaItem->sync_type == config('const.SYNC_TYPE.PATCH')) {
                // 登録・変更
                $this->_createMediaItem2($gmbService, $gmbApiService, $rsMediaItem);
            } else if ($rsMediaItem->sync_type == config('const.SYNC_TYPE.DELETE')) {
                // 削除
                $this->_deleteMediaItem2($gmbService, $gmbApiService, $rsMediaItem);
            }
        }

        // ログ出力
        $this->_proc_exit = 0;
        $this->_exception = "";
        $this->_detail = sprintf("mediaitem_regist_count=%d, mediaitem_delete_count=%d, mediaitem_exception_count=%d", 
                                $this->_mediaitem_regist_count, $this->_mediaitem_delete_count, $this->_mediaitem_exception_count);
        $this->_logging($gmbApiService);
    }

    // 写真の登録
    private function _createMediaItem2($gmbService, $gmbApiService, $rsMediaItem) {

        $media_item2_id= $rsMediaItem->media_item2_id;
        $gmbAccountId= $rsMediaItem->gmb_account_id;
        $gmbLocationId = $rsMediaItem->gmb_location_id;
        $parent = sprintf('accounts/%s/locations/%s', $gmbAccountId, $gmbLocationId);
        $this->_debug('登録 ' .$parent);

        try {

            $media = new Google_Service_MyBusiness_MediaItem();
            $media->setMediaFormat($rsMediaItem->gmb_media_format);
            
            $locationAssociation = new Google_Service_MyBusiness_LocationAssociation();
            $locationAssociation->setCategory($rsMediaItem->gmb_location_association_category);
          //  $locationAssociation->setCategory('CATEGORY_UNSPECIFIED');
            $media->setLocationAssociation($locationAssociation);
            
            $media->setDescription($rsMediaItem->gmb_description);
            $media->setSourceUrl($rsMediaItem->gmb_source_url);

            //$this->_debug($newPost);
            $gmb = $gmbService->accounts_locations_media->create($parent, $media);
            if ($gmb) {
                // 投稿成功
                $mediaItem = MediaItem2::where('media_item2_id', '=', $media_item2_id)
                                        ->first();
                if ($mediaItem != null) {
                    $nameAry = explode("/", $gmb['name']);

                    $mediaItem->gmb_media_key  = $nameAry[5];
                    $mediaItem->sync_status  = config('const.SYNC_STATUS.SYNCED');
                    $mediaItem->sync_time  = Carbon::now();
                    $mediaItem->scheduled_sync_time = null;
                    $mediaItem->update_user_id  = 0;
                    $mediaItem->save();

                    $this->_mediaitem_regist_count ++;
                    unset($nameAry);

                }
            }
            unset($gmb);

        } catch ( Google_Service_Exception $e ) {
            // 登録失敗
            $mediaItem = MediaItem2::where('media_item2_id', '=', $media_item2_id)
                                    ->first();
            if ($mediaItem != null) {
                $mediaItem->sync_status  = config('const.SYNC_STATUS.CANCEL');
                $mediaItem->sync_time  = Carbon::now();
                $mediaItem->scheduled_sync_time = null;
                $mediaItem->update_user_id  = 0;
                $mediaItem->save();

                $this->_mediaitem_exception_count ++;
            }

        } finally {
            $mediaItem = null;
        }

    }

    // 投稿画像の削除
    private function _deleteMediaItem2($gmbService, $gmbApiService, $rsMediaItem) {

        $mediaItemId= $rsMediaItem->media_item2_id;
        $gmbAccountId= $rsMediaItem->gmb_account_id;
        $gmbLocationId = $rsMediaItem->gmb_location_id;
        $gmbMediaKey = $rsMediaItem->gmb_media_key;
        $parent = sprintf('accounts/%s/locations/%s/media/%s', $gmbAccountId, $gmbLocationId, $gmbMediaKey);
        $this->_debug('削除 ' .$parent);

        try {

            $gmb = $gmbService->accounts_locations_media->delete($parent);
            // Google_Service_MyBusiness_MybusinessEmpty
            $mediaItem = MediaItem2::where('media_item2_id', '=', $mediaItemId)
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
            unset($gmb);

        } catch ( Google_Service_Exception $e ) {
            // ログ
            $this->_proc_exit = -1;
            $this->_class_function = "GmbApiMediaItemRegistService._deleteMediaItem2";
            $this->_detail = sprintf("parent=%s", $parent);
            $this->_exception = $e->getMessage();
            $this->_logging($gmbApiService);

        } finally {
            $mediaItem = null;
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