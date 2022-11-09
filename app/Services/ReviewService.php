<?php

namespace App\Services;

use DB;
use App\Review;
use App\ReviewReply;
use App\ReviewReplyTemplate;
use Illuminate\Http\Request;

class ReviewService
{
    public function find($reviewId)
    {
        return Review::findOrFail($reviewId);
    }

    public function search(Request $request)
    {
        $reviews = $this->getCondition($request);
        return $reviews->paginate(config('const.PAGINATE_LIMIT'));
    }

    public function findTemplates()
    {
        return ReviewReplyTemplate::Active()->ManualReply()->select('review_reply_template_id', 'template_name')
            ->pluck('template_name', 'review_reply_template_id');
    }

    public function export(Request $request)
    {
        $reviews = $this->getCondition($request);
        $reviews = $reviews->select(config('const.CSV_ITEMS.REVIEW'))->get();
        if (!empty($reviews)) {
            $reviews = $reviews->toArray();
        } else {
            $reviews = [];
        }
        return outPutCsv($reviews, config('const.CSV_HEADERS.REVIEW'),
            config('const.EXPORT_REVIEWS_FILE_NAME') . '_' . date('ymd'));
    }

    public function reply(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->filled('review_reply_id')) {
                ReviewReply::findOrFail($request->input('review_reply_id'))
                    ->fill(['gmb_comment' => $request->input('gmb_comment')])->save();
            } else {
                $reviewReply = ReviewReply::create($request->all());
                Review::findOrFail($request->review_id)
                    ->fill(['review_reply_id' => $reviewReply->review_reply_id])->save();
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }
    }

    public function getCondition(Request $request)
    {
        $reviews = Review::Active();
        if ($request->filled('stDate') && $request->filled('endDate')) {
            $reviews = $reviews->BetweenCreateDate($request->input('stDate'), $request->input('endDate') . ' 23:59:59');
        } elseif ($request->filled('stDate')) {
            $reviews = $reviews->ByCreateStDate($request->input('stDate'));
        } elseif ($request->filled('endDate')) {
            $reviews = $reviews->ByCreateEndDate($request->input('endDate') . ' 23:59:59');
        }

        if ($request->filled('rate')) {
            $reviews = $reviews->ByStarRating($request->input('rate'));
        }
        if ($request->filled('replyStatus')) {
            switch ($request->input('replyStatus')) {
                case config('const.FLG_OFF'):
                    $reviews = $reviews->ReviewReplyIdIsNull($request->input('replyStatus'));
                    break;
                case config('const.FLG_ON'):
                    $reviews = $reviews->ReviewReplyIdIsNotNull($request->input('replyStatus'));
                    break;
            }
        }
        return $reviews;
    }

}
