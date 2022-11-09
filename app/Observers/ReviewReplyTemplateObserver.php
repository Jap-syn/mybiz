<?php

namespace App\Observers;

use Auth;
use App\ReviewReplyTemplate;

class ReviewReplyTemplateObserver
{
    /**
     * Handle the review reply "created" event.
     *
     * @param  \App\ReviewReplyTemplate  $reviewReplyTemplate
     * @return void
     */
    public function created(ReviewReplyTemplate $reviewReplyTemplate)
    {
        //
        $reviewReplyTemplate->create_user_id = Auth::id();
        $reviewReplyTemplate->update_user_id = Auth::id();
    }

    /**
     * Handle the review reply "updated" event.
     *
     * @param  \App\ReviewReplyTemplate  $reviewReplyTemplate
     * @return void
     */
    public function updated(ReviewReplyTemplate $reviewReplyTemplate)
    {
        //
        $reviewReplyTemplate->create_user_id = Auth::id();
        $reviewReplyTemplate->update_user_id = Auth::id();
    }

    public function saving(ReviewReplyTemplate $reviewReplyTemplate)
    {
        $reviewReplyTemplate->create_user_id = Auth::id();
        $reviewReplyTemplate->update_user_id = Auth::id();
    }

    public function saved(ReviewReplyTemplate $reviewReplyTemplate)
    {
        $reviewReplyTemplate->create_user_id = Auth::id();
        $reviewReplyTemplate->update_user_id = Auth::id();
    }

    /**
     * Handle the review reply "deleted" event.
     *
     * @param  \App\ReviewReplyTemplate  $reviewReplyTemplate
     * @return void
     */
    public function deleted(ReviewReplyTemplate $reviewReplyTemplate)
    {
        //
    }

    /**
     * Handle the review reply "restored" event.
     *
     * @param  \App\ReviewReplyTemplate  $reviewReplyTemplate
     * @return void
     */
    public function restored(ReviewReplyTemplate $reviewReplyTemplate)
    {
        //
    }

    /**
     * Handle the review reply "force deleted" event.
     *
     * @param  \App\ReviewReplyTemplate  $reviewReplyTemplate
     * @return void
     */
    public function forceDeleted(ReviewReplyTemplate $reviewReplyTemplate)
    {
        //
    }
}
