<?php

namespace App\Http\Controllers\Api\v1_0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use App;
use App\Review;
use App\ReviewReply;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        DB::enableQueryLog();

        $gmb_account_id = $_GET['gmb_account_id'];
        $gmb_location_id = $_GET['gmb_location_id'];
        $from = date('Y-') .substr($_GET['from'], 0, 2) .'-' .substr($_GET['from'], -2);
        $to = date('Y-') .substr($_GET['to'], 0, 2) .'-' .substr($_GET['to'], -2);

        switch ($_GET['star']){
        case "1":
            $star = 'ONE';
            break;
        case "2":
            $star = 'TWO';
            break;
        case "3":
            $star = 'THREE';
            break;
        case "4":
            $star = 'FOUR';
            break;
        case "5":
            $star = 'FIVE';
            break;
        default:
            $star = 'ONE';
        }

        $status = '=';      // 未返信
        if ($_GET['status'] == "1") {
            $status = '!='; // 返信済
        }

        $reviews = Review::selectRaw('review_id,gmb_reviewer_display_name,gmb_star_rating,SUBSTRING(gmb_comment,1,50) gmb_comment')
                            ->active()
                            ->where('gmb_account_id', '=', $gmb_account_id)
                            ->where('gmb_location_id', '=', $gmb_location_id)
                            ->where('gmb_star_rating', '=', $star)
                            ->where('gmb_review_reply_comment', $status, '')
                            ->whereBetween("gmb_create_time", array($from." 00:00:00", $to." 23:59:59"))
                            ->limit(100)->get();

        logger()->error(DB::getQueryLog());

        return response()->json([
            'reviews' => $reviews,
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

        $gmb_account_id = $_GET['gmb_account_id'];
        $gmb_location_id = $_GET['gmb_location_id'];
        $review_id = $_GET['review_id'];
        
        $json = file_get_contents("php://input");
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
        $dataAry = json_decode($json,true);
        
        $result = False;
        $reviewReply = ReviewReply::active()->where('review_id', '=', $review_id)->first();
        if ($reviewReply == NULL) {
            // 新規作成
            $newReply = new ReviewReply;
            $newReply->review_id  = $review_id;
            $newReply->gmb_comment  = $dataAry['reply_comment'];
            $newReply->is_deleted  = 0;
            $newReply->sync_type  = config('const.SYNC_TYPE.CREATE');
            $newReply->sync_status  = config('const.SYNC_STATUS.SYNCED');
            $newReply->create_user_id  = 0;
            $result = $newReply->save();

        } else {
            // 更新
            $reviewReply->gmb_comment  = $dataAry['reply_comment'];
            $result = $reviewReply->save();
        }

        logger()->error(DB::getQueryLog());

        if ($result) {
            return response()->json([
                'review' => "success",
                ], 200); 
        } else {
            return response()->json([
                'review' => "failed",
                ], 200); 
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        DB::enableQueryLog();

        $review_id = $id;  
        $gmb_account_id = $_GET['gmb_account_id'];
        $gmb_location_id = $_GET['gmb_location_id'];

        $review = Review::active()
                            ->leftJoin('review_replies','reviews.review_id','=','review_replies.review_id')
                            ->where('reviews.review_id', '=', $review_id)
                            ->where('gmb_account_id', '=', $gmb_account_id)
                            ->where('gmb_location_id', '=', $gmb_location_id)
                            ->first();

        logger()->error(DB::getQueryLog());

        logger()->error($review);

        if ($review != NULL) {
            return response()->json([
                'review' => $review,
                ], 200); 
        } else {
            return response()->json([
                'review' => 'not found',
                ], 404); 
        }

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
        return response()->json([
            'review' => 'review update',
            ], 200);   
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
