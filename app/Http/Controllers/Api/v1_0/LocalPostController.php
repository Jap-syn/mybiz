<?php

namespace App\Http\Controllers\Api\v1_0;

use DB;
use App;
use App\Account;
use App\Location;
use App\LocalPost;
use App\LocalPostGroup;
use App\MediaItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;
use Carbon\Carbon;

class LocalPostController extends Controller
{

    public function index_xxx()
    {
        logger()->error('index ------2');

        if (App::environment('staging')) {
            DB::enableQueryLog();
        }


        $dsLocalPost = LocalPost::queued()
            ->where('scheduled_sync_time', '<=', Carbon::now())
            ->get();

        logger()->error($dsLocalPost);

        /*
        $sync_status = config('const.SYNC_STATUS.SYNCED');

        MediaItem::where('local_post_group_id', 95)
            ->update ([
                'gmb_media_key' => 'test',
                'sync_status' => $sync_status,
                'sync_time' => Carbon::now(),
                'update_time' => Carbon::now(),
                'update_user_id' => 0
            ]);
        */

        if (App::environment('staging')) {
            logger()->error(DB::getQueryLog());
        }

        return response()->json([
            'result' => "ok",
            ], 200);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gmb_account_id = $_GET['gmb_account_id'];
        $gmb_topic_type= $_GET['gmb_topic_type'];      
        $search_title = mb_convert_encoding($_GET['search_title'], 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');

     //   DB::enableQueryLog();

        $localPostGroups = LocalPostGroup::select('id','event_title')
                                ->active()
                                ->where('gmb_account_id', '=', $gmb_account_id)
                                ->where('topic_type', '=', $gmb_topic_type)
                                ->where('event_title', 'LIKE', "%$search_title%")
                                ->limit(100)->get();

    //    logger()->error(DB::getQueryLog());

        return response()->json([
            'localPostGroups' => $localPostGroups,
            ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        DB::enableQueryLog();

        $resText = "";
        $resCode = 200;
        $isUploaded = false;
        $s3_object_url = "";
        $disk = Storage::disk('s3');

        $json = file_get_contents("php://input");
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $dataAry = json_decode($json,true);
        
        logger()->error($dataAry);

        try {

            $account = Account::select('account_id')
                                ->active()->where('gmb_account_id', '=', $_GET['gmb_account_id'])->first();
            if ($account == NULL) {
                $resText = "Active account not found.";
                return response()->json([
                    'result' => $resText,
                    ], 500);
            } 

            DB::beginTransaction();

            // 投稿グループ登録
            $localPostGroup = new LocalPostGroup;
            $localPostGroup->account_id = $account->account_id;
            $localPostGroup->gmb_account_id = $_GET['gmb_account_id'];
            $localPostGroup->topic_type = $dataAry['topicType'];
            $localPostGroup->event_title = $dataAry['event_title'];

            $event_start_date = NULL;
            if (isset($dataAry['event_start_date']) && $dataAry['event_start_date'] != ""){
                $event_start_date = $this->_checkJsonDateTime($dataAry['event_start_date'], $dataAry['event_start_time']);
            }
            
            $event_end_time = NULL;
            if (isset($dataAry['event_end_date']) && $dataAry['event_end_date'] != ""){
                $event_end_time = $this->_checkJsonDateTime($dataAry['event_end_date'], $dataAry['event_end_time']);
            }

            $localPostGroup->event_start_time = $event_start_date;
            $localPostGroup->event_end_time = $event_end_time;
            $localPostGroup->is_deleted  = 0;
            $localPostGroup->create_user_id  = 0;
            $localPostGroup->save();
            $localPostGroupId = $localPostGroup->id;

            $gmb_source_url = "";
            $gmbLocationIdAry = explode(',',$dataAry["gmb_location_ids"]);
            foreach ($gmbLocationIdAry as $gmbLocationId) {
              
                // 画像は1回だけs3にアップロードしてs3_object_urlを複数店舗で使い回す
                if ($isUploaded == false) {
                    if ($dataAry["file"] != "") {
                        $image = base64_decode($dataAry["file"]);

                        $s3_object_url = "/gmb/media/" .strtolower($dataAry["topicType"]);
                        $s3_object_url .= "/" .$_GET['gmb_account_id'] ."/" .$localPostGroupId ."/" .date("YmdHis") .uniqid() ."." .$dataAry["extension"];
                        $path = $disk->put($s3_object_url, $image, 'public');
                        $gmb_source_url = preg_replace("/\/1\z/", "", $disk->url($path)) .$s3_object_url;
                        $isUploaded = $disk->exists($s3_object_url);
                    }
                }
             
                $location = Location::select(['location_id'])
                            ->active()
                            ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                            ->where('gmb_location_id', '=', str_replace("id", "", $gmbLocationId))
                            ->first();

                if ($location != NULL) {
                    $localPost = new LocalPost;
                    $localPost->location_id = $location->location_id;
                    $localPost->local_post_group_id = $localPostGroupId;
                    $localPost->gmb_account_id = $_GET['gmb_account_id'];
                    $localPost->gmb_location_id = str_replace("id", "", $gmbLocationId);
                    $localPost->gmb_local_post_id = "";
                    $localPost->gmb_language_code = $dataAry['language_code'];
                    $localPost->gmb_summary = $dataAry['summary'];
                    if ($dataAry['action_type'] != "") $localPost->gmb_action_type = $dataAry['action_type'];
                    $localPost->gmb_action_type_url = $dataAry['action_type_url'];
                    $localPost->gmb_event_title = $dataAry['event_title'];
                    $localPost->gmb_event_start_time = $event_start_date;
                    $localPost->gmb_event_end_time = $event_end_time;
                    $localPost->gmb_topic_type = $dataAry['topicType'];
                    if ($dataAry['alertType'] != "") $localPost->gmb_alert_type = $dataAry['alertType'];
                    $localPost->is_deleted  = config('const.IS_DELETED.OFF');
                    $localPost->sync_type = config('const.SYNC_TYPE.CREATE');
                    $localPost->sync_status = config('const.SYNC_STATUS.QUEUED');
                    $localPost->scheduled_sync_time = DB::raw("STR_TO_DATE('" .$dataAry['scheduled_sync_time'] ."','%Y%m%d%H%i%s')");
                    $localPost->create_user_id  = 0;
                    $localPost->save();
                    $localPostId = $localPost->local_post_id;
                    
                    if ($isUploaded) {
                        $mediaItem = new MediaItem;
                        $mediaItem->local_post_id = $localPostId;
                        $mediaItem->local_post_group_id = $localPostGroupId;
                        $mediaItem->gmb_account_id = $_GET['gmb_account_id'];
                        $mediaItem->gmb_location_id = str_replace("id", "", $gmbLocationId);
                        $mediaItem->gmb_media_key = "";
                        $mediaItem->gmb_media_format = $dataAry['media_format'];
                        $mediaItem->gmb_location_association_category = $dataAry['location_association_category'];
                        $mediaItem->gmb_source_url = $gmb_source_url;
                        $mediaItem->s3_object_url = $s3_object_url;
                        $mediaItem->is_deleted  = config('const.IS_DELETED.OFF');
                        $mediaItem->sync_type = config('const.SYNC_TYPE.CREATE');
                        $mediaItem->sync_status = config('const.SYNC_STATUS.QUEUED');
                        $mediaItem->scheduled_sync_time = DB::raw("STR_TO_DATE('" .$dataAry['scheduled_sync_time'] ."','%Y%m%d%H%i%s')");
                        $mediaItem->create_user_id  = 0;
                        $mediaItem->save();
                    }
                }
            }

            DB::commit();
            $resText = "success";

            logger()->error(DB::getQueryLog());

        } catch ( Exception $e ) {
            DB::rollBack();

            if ($s3_object_url != "" && $isUploaded) {
                $disk->delete($s3_object_url);
            }

            $resCode = 500;
            $resText = "exception: " .$e->getMessage();
            logger()->error($e->getMessage());
        }

        return response()->json([
            'result' => $resText,
            ], $resCode);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $local_post_group_id = $id;  
        $gmb_account_id = $_GET['gmb_account_id'];

        $localPost = LocalPost::select()
                                ->active()
                                ->where('local_post_group_id', '=', $local_post_group_id)->first();    
        if ($localPost == NULL) {
            $resText = "Active LocalPost not found.";
            return response()->json([
                'result' => $resText,
                ], 500);
        } 

        $localPosts = LocalPost::select(['gmb_location_id'])
                                ->active()
                                ->where('local_post_group_id', '=', $local_post_group_id)->get();

        $mediaItem = MediaItem::active()
                                ->where('local_post_id', '=', $localPost->local_post_id)->get();

        /*
        $mediaItem = MediaItem::join('locations', function ($join) {
                                    $join->on('location_local_posts.location_id', '=', 'locations.location_id')
                                        ->where('locations.is_deleted', '=', config('const.IS_DELETED.OFF'));
                                })
                                ->where('local_post_id', '=', $local_post_id)->get();
        */
        return response()->json([
            'localPost' => $localPost,
            'gmbLocationIds' => $localPosts,
            'mediaItem' => $mediaItem,
            ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // 注意：引数idはメソッドを呼び出すためのダミーなので使用しない

        $resText = "";
        $resCode = 200;
        $isUploaded = false;
        $isCreated = false;
        $s3_object_url = "";
        $disk = Storage::disk('s3');

        $json = file_get_contents("php://input");
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $dataAry = json_decode($json,true);
        
        try {

            DB::enableQueryLog();

            $event_start_date = "";
            if (isset($dataAry['event_start_date'])){
                $event_start_date = $this->_checkJsonDateTime($dataAry['event_start_date'], $dataAry['event_start_time']);
            }
            
            $event_end_time = "";
            if (isset($dataAry['event_end_date'])){
                $event_end_time = $this->_checkJsonDateTime($dataAry['event_end_date'], $dataAry['event_end_time']);
            }

            DB::beginTransaction();

            // 投稿グループの内容を更新する
            LocalPostGroup::active()
                            ->where('id', '=', $dataAry["local_post_group_id"])
                            ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                            ->update(['topic_type' => $dataAry['topicType']
                                    , 'event_title' => $dataAry['event_title']
                                    , 'event_start_time' => $event_start_date
                                    , 'event_end_time' => $event_end_time
                                    , 'update_user_id' => 0
                                    , 'update_time' => Carbon::now()]);

            // 一旦すべてを論理削除する
            LocalPost::active()
                        ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                        ->where('local_post_group_id', '=', $dataAry["local_post_group_id"])
                        ->update(['is_deleted' => config('const.IS_DELETED.PROCESSING')]);

            $gmb_source_url = "";
            $gmbLocationIdAry = explode(',',$dataAry["gmb_location_ids"]);
            foreach ($gmbLocationIdAry as $gmbLocationId) {
                $gmbLocationId = str_replace("id", "", $gmbLocationId);

                // 新しい画像が選択されていれば、画像を置き換える
                if ($isUploaded == false) {
                    if ($dataAry["file"] != "") {
                        // すでにAPI連携で新規作成済の場合、古い画像になるので削除する
                        if ($is_created) {
                            MediaItem::active()
                                        ->where('local_post_id', '=', $dataAry["local_post_id"])
                                        ->update(['is_deleted' => config('const.IS_DELETED.ON')
                                                , 'sync_type' => config('const.SYNC_TYPE.DELETE')
                                                , 'sync_status' => config('const.SYNC_STATUS.QUEUED')
                                                , 'scheduled_sync_time' => Carbon::now()]);
                        }

                        $image = base64_decode($dataAry["file"]);
                        $s3_object_url = "/gmb/media/" .strtolower($dataAry["topicType"]);
                        $s3_object_url .= "/" .$_GET['gmb_account_id'] ."/" .$dataAry["local_post_group_id"] ."/" .date("YmdHis") .uniqid() ."." .$dataAry["extension"];
                        $path = $disk->put($s3_object_url, $image, 'public');
                        $gmb_source_url = preg_replace("/\/1\z/", "", $disk->url($path)) .$s3_object_url;
                        $isUploaded = $disk->exists($s3_object_url);
                    }
                }

                // 存在チェックと、API連携前に変更した場合は、sync_type=CREATEのままにするためにすでにAPI連携済かどうかをチェック。
                $localPost = LocalPost::processing()
                                    ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                                    ->where('gmb_location_id', '=', $gmbLocationId)
                                    ->where('local_post_group_id', '=', $dataAry["local_post_group_id"])
                                    ->first();

                if ($localPost == NULL) {
                    // 新規作成
                    logger()->error("update 新規作成");

                    $location = Location::select(['location_id'])
                                        ->active()
                                        ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                                        ->where('gmb_location_id', '=', $gmbLocationId)
                                        ->first();

                    if ($location != NULL) {
                        $localPost = new LocalPost;
                        $localPost->location_id = $location->location_id;
                        $localPost->local_post_group_id = $dataAry["local_post_group_id"];
                        $localPost->gmb_account_id = $_GET['gmb_account_id'];
                        $localPost->gmb_location_id = str_replace("id", "", $gmbLocationId);
                        $localPost->gmb_local_post_id = "";
                        $localPost->gmb_language_code = $dataAry['language_code'];
                        $localPost->gmb_summary = $dataAry['summary'];
                        if ($dataAry['action_type'] != "") $localPost->gmb_action_type = $dataAry['action_type'];
                        $localPost->gmb_action_type_url = $dataAry['action_type_url'];
                        $localPost->gmb_event_title = $dataAry['event_title'];
                        if ($event_start_date != "") $localPost->gmb_event_start_time=$event_start_date;
                        if ($event_end_time != "") $localPost->gmb_event_end_time = $event_end_time;
                        $localPost->gmb_topic_type = $dataAry['topicType'];
                        if ($dataAry['alertType'] != "") $localPost->gmb_alert_type = $dataAry['alertType'];
                        $localPost->is_deleted  = config('const.IS_DELETED.OFF');
                        $localPost->sync_type = config('const.SYNC_TYPE.CREATE');
                        $localPost->sync_status = config('const.SYNC_STATUS.QUEUED');
                        $localPost->scheduled_sync_time = DB::raw("STR_TO_DATE('" .$dataAry['scheduled_sync_time'] ."','%Y%m%d%H%i%s')");
                        $localPost->create_user_id  = 0;
                        $localPost->save();
                        $localPostId = $localPost->local_post_id;
                        
                        if ($isUploaded) {
                            $mediaItem = new MediaItem;
                            $mediaItem->local_post_id = $localPostId;
                            $mediaItem->local_post_group_id = $dataAry["local_post_group_id"];
                            $mediaItem->gmb_account_id = $_GET['gmb_account_id'];
                            $mediaItem->gmb_location_id = str_replace("id", "", $gmbLocationId);
                            $mediaItem->gmb_media_key = "";
                            $mediaItem->gmb_media_format = $dataAry['media_format'];
                            $mediaItem->gmb_location_association_category = $dataAry['location_association_category'];
                            $mediaItem->gmb_source_url = $gmb_source_url;
                            $mediaItem->s3_object_url = $s3_object_url;
                            $mediaItem->is_deleted  = config('const.IS_DELETED.OFF');
                            $mediaItem->sync_type = config('const.SYNC_TYPE.CREATE');
                            $mediaItem->sync_status = config('const.SYNC_STATUS.QUEUED');
                            $mediaItem->scheduled_sync_time = DB::raw("STR_TO_DATE('" .$dataAry['scheduled_sync_time'] ."','%Y%m%d%H%i%s')");
                            $mediaItem->create_user_id  = 0;
                            $mediaItem->save();
                        }
                    }

                } else {

                    // 更新
                    $localPost->gmb_language_code = $dataAry['language_code'];
                    $localPost->gmb_summary = $dataAry['summary'];
                    if ($dataAry['action_type'] != "") $localPost->gmb_action_type = $dataAry['action_type'];
                    $localPost->gmb_action_type_url = $dataAry['action_type_url'];
                    $localPost->gmb_event_title = $dataAry['event_title'];
                    if ($event_start_date != "") $localPost->gmb_event_start_time=$event_start_date;
                    if ($event_end_time != "") $localPost->gmb_event_end_time = $event_end_time;
                    $localPost->gmb_topic_type = $dataAry['topicType'];
                    if ($dataAry['alertType'] != "") $localPost->gmb_alert_type = $dataAry['alertType'];
                    $localPost->is_deleted  = config('const.IS_DELETED.OFF');

                    if ($localPost->sync_type == config('const.SYNC_TYPE.CREATE') 
                        && ($localPost->sync_status == config('const.SYNC_STATUS.QUEUED') || $localPost->sync_status == config('const.SYNC_STATUS.FAILED'))) {

                            logger()->error("update 更新111　".$localPost->local_post_id);
                        }
                    else {
                        // すでにAPI連携で新規作成済の場合は、PATCHに設定する。新規作成前ならCREATEのままにする。
                        $localPost->sync_type = config('const.SYNC_TYPE.PATCH');
                        $isCreated = true;

                        logger()->error("update 更新222　".$localPost->local_post_id);
                    }

                    $localPost->sync_status = config('const.SYNC_STATUS.QUEUED');
                    $localPost->scheduled_sync_time = DB::raw("STR_TO_DATE('" .$dataAry['scheduled_sync_time'] ."','%Y%m%d%H%i%s')");
                    $localPost->update_user_id  = 0;
                    $localPost->save();

                    if ($isUploaded) {
                        // アップロードした画像は、gmb_media_keyが変わるので新規作成
                        $mediaItem = new MediaItem;
                        $mediaItem->local_post_id = $localPost->local_post_id;
                        $mediaItem->local_post_group_id = $dataAry["local_post_group_id"];
                        $mediaItem->gmb_account_id = $_GET['gmb_account_id'];
                        $mediaItem->gmb_location_id = str_replace("id", "", $gmbLocationId);
                        $mediaItem->gmb_media_key = "";
                        $mediaItem->gmb_media_format = $dataAry['media_format'];
                        $mediaItem->gmb_location_association_category = $dataAry['location_association_category'];
                        $mediaItem->gmb_source_url = $gmb_source_url;
                        $mediaItem->s3_object_url = $s3_object_url;
                        $mediaItem->is_deleted  = config('const.IS_DELETED.OFF');
                        $mediaItem->sync_type = config('const.SYNC_TYPE.CREATE');
                        $mediaItem->sync_status = config('const.SYNC_STATUS.QUEUED');
                        $mediaItem->scheduled_sync_time = DB::raw("STR_TO_DATE('" .$dataAry['scheduled_sync_time'] ."','%Y%m%d%H%i%s')");
                        $mediaItem->create_user_id  = 0;
                        $mediaItem->save();
                    }
                }
            }

            // 更新されなかった投稿
            if ($isCreated) {

                logger()->error("update isCreated=true");

                // すでにAPI連携で新規作成済の場合は、投稿先から外されたため論理削除
                LocalPost::processing()
                        ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                        ->where('local_post_group_id', '=', $dataAry["local_post_group_id"])
                        ->update(['is_deleted' => config('const.IS_DELETED.ON')
                                ,'sync_type' => config('const.SYNC_TYPE.DELETE')
                                ,'sync_status' => config('const.SYNC_STATUS.QUEUED')
                                ,'scheduled_sync_time' => DB::raw("STR_TO_DATE('" .$dataAry['scheduled_sync_time'] ."','%Y%m%d%H%i%s')")
                                ,'update_user_id' => 0
                                ,'update_time' => Carbon::now()]);

            } else {
                // まだAPI連携前で新規作成されていない場合は、物理的に削除

                logger()->error("update isCreated=false");

                $local_post_group_id = $dataAry["local_post_group_id"];
                
                MediaItem::where('local_post_group_id', '=', $local_post_group_id)
                            ->whereIn('local_post_id',
                                function ($query) use ($local_post_group_id) {
                                    $query->select('local_post_id')
                                    ->from('local_posts')
                                    ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                                    ->where('is_deleted', '=', config('const.IS_DELETED.PROCESSING'))
                                    ->where('local_post_group_id', '=', $local_post_group_id);
                                })
                            ->delete();

                LocalPost::processing()
                        ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                        ->where('local_post_group_id', '=', $local_post_group_id)
                        ->delete();
            }

            DB::commit();
            $resText = "success";

            logger()->error(DB::getQueryLog());

        } catch ( Exception $e ) {
            DB::rollBack();

            if ($s3_object_url != "" && $isUploaded) {
                $disk->delete($s3_object_url);
            }

            $resCode = 500;
            $resText = "exception: " .$e->getMessage();
            logger()->error($e->getMessage());
        }

        return response()->json([
            'result' => $resText,
            ], $resCode);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 注意：引数idはダミー文字列が渡されるため使用しない
        $resText = "";
        $resCode = 200;
        $disk = Storage::disk('s3');

        $json = file_get_contents("php://input");
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $dataAry = json_decode($json,true);
        
        try {

            $gmbLocationIdAry = array();
            $ary = explode(',',$dataAry["gmb_location_ids"]);
            foreach ($ary as $gmbLocationId) {
                $gmbLocationIdAry[] = str_replace("id", "", $gmbLocationId);
            }

            if (App::environment('staging')) {
                DB::enableQueryLog();
            }

            // すでにAPI連携済かどうか。API連携前なら対象レコードはすべて物理的に削除する。
            $localPost = LocalPost::select(['sync_status'])
                                    ->active()
                                    ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                                    ->whereIn('gmb_location_id', $gmbLocationIdAry)
                                    ->where('local_post_group_id', '=', $dataAry["local_post_group_id"])
                                    ->first();

            $isCreated = false;
            if ($localPost != NULL) {
                if ($localPost->sync_status == config('const.SYNC_STATUS.SYNCED')) {
                    $isCreated = true;
                }
            }
            
            DB::beginTransaction();

            if ($isCreated) {
                // すでにAPI連携で新規作成済の場合は、論理削除にしてAPI連携でDELETE発行
                MediaItem::active()
                            ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                            ->whereIn('gmb_location_id', $gmbLocationIdAry)
                            ->where('local_post_group_id', '=', $dataAry['local_post_group_id'])
                            ->update(['is_deleted' => config('const.IS_DELETED.ON')
                                    , 'sync_type' => config('const.SYNC_TYPE.DELETE')
                                    , 'sync_status' => config('const.SYNC_STATUS.QUEUED')
                                    , 'scheduled_sync_time' => Carbon::now()]);

                LocalPost::active()
                            ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                            ->whereIn('gmb_location_id', $gmbLocationIdAry)
                            ->where('local_post_group_id', '=', $dataAry["local_post_group_id"])
                            ->update(['is_deleted' => config('const.IS_DELETED.ON')
                                    , 'sync_type' => config('const.SYNC_TYPE.DELETE')
                                    , 'sync_status' => config('const.SYNC_STATUS.QUEUED')
                                    , 'scheduled_sync_time' => Carbon::now()]);

                LocalPostGroup::active()
                                ->where('id', '=', $dataAry["local_post_group_id"])
                                ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                                ->update(['is_deleted' => config('const.IS_DELETED.ON')
                                        , 'update_user_id' => 0
                                        , 'update_time' => Carbon::now()]);

            } else {

                // まだAPI連携前で新規作成されていない場合は、物理的に削除してAPI連携しないようにする。
                MediaItem::active()
                            ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                            ->whereIn('gmb_location_id', $gmbLocationIdAry)
                            ->where('local_post_group_id', '=', $dataAry['local_post_group_id'])
                            ->delete();

                LocalPost::active()
                            ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                            ->whereIn('gmb_location_id', $gmbLocationIdAry)
                            ->where('local_post_group_id', '=', $dataAry["local_post_group_id"])
                            ->delete();

                LocalPostGroup::active()
                                ->where('id', '=', $dataAry["local_post_group_id"])
                                ->where('gmb_account_id', '=', $_GET['gmb_account_id'])
                                ->delete();
            }

            DB::commit();

            if (App::environment('staging')) {
                logger()->error(DB::getQueryLog());
            }

            // アップロード画像を削除
            if ($dataAry["s3_object_url"] != "") {
                $disk->delete($dataAry["s3_object_url"]);
            }

            $resText = "success";

        } catch ( Exception $e ) {
            DB::rollBack();
            $resCode = 500;
            $resText = "exception: " .$e->getMessage();
            logger()->error($e->getMessage());
        }

        return response()->json([
            'result' => $resText,
            ], $resCode);
    }

    private function _checkJsonDateTime($date, $time) {
        if ($time == "") $time = "000000";
        return DB::raw("STR_TO_DATE('" .$date  ." " .$time ."','%Y%m%d %H%i%s')");
    }


         /*
        ob_flush();
        ob_start();
        var_dump($_GET['gmb_account_id']);
        file_put_contents("/var/www/phplaravel/public/store.txt", ob_get_flush());
        */   
}
