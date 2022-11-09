<?php

namespace App\Services;

use App\ReviewReplyTemplate;
use Illuminate\Http\Request;

class ReviewReplyTemplateService
{
    public function find($reviewId)
    {
        return ReviewReplyTemplate::findOrFail($reviewId);
    }

    public function search(Request $request)
    {
        $templates = $this->getCondition($request);
        return $templates->get();
    }

    public function store(Request $request)
    {
        try {
            return ReviewReplyTemplate::create(array_filter($request->all(), "strlen"));
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return false;
        }
    }

    public function update(Request $request)
    {
        try {
            return ReviewReplyTemplate::findOrFail($request->input('review_reply_template_id'))
                ->fill(array_filter($request->all(), "strlen"))->save();
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return false;
        }
    }

    public function delete(Request $request)
    {
        try {
            return ReviewReplyTemplate::findOrFail($request->input('review_reply_template_id'))
                ->fill(['is_deleted' => config('const.FLG_ON')])->save();
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return false;
        }
    }

    public function getCondition(Request $request)
    {
        $templates = ReviewReplyTemplate::Active();
        if ($request->filled('is_autoreply_template')) {
            switch ($request->input('is_autoreply_template')) {
                case config('const.FLG_OFF'):
                    $templates = $templates->ManualReply();
                    break;
                case config('const.FLG_ON'):
                    $templates = $templates->AutoReply();
                    break;
            }
        }
        if ($request->filled('target_star_rating')) {
            $templates = $templates->ByTargetStarRating($request->input('target_star_rating'));
        }
        return $templates;
    }

}
