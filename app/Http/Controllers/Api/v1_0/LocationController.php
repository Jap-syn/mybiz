<?php

namespace App\Http\Controllers\Api\v1_0;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Location;
use Carbon\Carbon;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $location = Location::active()
                            ->where('gmb_account_id', $_GET['gmb_account_id'])
                            ->where('gmb_location_id', $_GET['gmb_location_id'])
                            ->first();

        return response()->json([
            'location' => $location,
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
        $resText = "";
        
        $json = file_get_contents("php://input");
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $dataAry = json_decode($json,true);
        
        try {

            DB::enableQueryLog();
            
            $location = Location::where('gmb_account_id', $_GET['gmb_account_id'])
                                ->where('gmb_location_id', $_GET['gmb_location_id'])
                                ->first();

            $location->gmb_profile_description = $dataAry['gmb_profile_description'];
            $location->sync_type = config('const.SYNC_TYPE.PATCH');
            $location->sync_status = config('const.SYNC_STATUS.QUEUED');
            $location->scheduled_sync_time = Carbon::now();
            $location->save();
            $resText = "success";

            logger()->error(DB::getQueryLog());

        } catch ( Exception $e ) { 
            logger()->error('store Exception: ' .$e->getMessage());
            $resText = "Exception: " .$e->getMessage();
        }

        return response()->json([
            'result' => $resText,
            ], 200);    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
