<?php

namespace App\Observers;

use Auth;
use App\ReviewReply;

class ReviewReplyObserver
{
    /**
     * Handle the review reply "created" event.
     *
     * @param  \App\ReviewReply  $reviewReply
     * @return void
     */
    public function created(ReviewReply $reviewReply)
    {
        //
        $reviewReply->create_user_id = Auth::id();
        $reviewReply->update_user_id = Auth::id();
    }

    /**
     * Handle the review reply "updated" event.
     *
     * @param  \App\ReviewReply  $reviewReply
     * @return void
     */
    public function updated(ReviewReply $reviewReply)
    {
        //
        $reviewReply->create_user_id = Auth::id();
        $reviewReply->update_user_id = Auth::id();
    }

    public function saving(ReviewReply $reviewReply)
    {
        $reviewReply->create_user_id = Auth::id();
        $reviewReply->update_user_id = Auth::id();
    }

    public function saved(ReviewReply $reviewReply)
    {
        $reviewReply->create_user_id = Auth::id();
        $reviewReply->update_user_id = Auth::id();
    }

    /**
     * Handle the review reply "deleted" event.
     *
     * @param  \App\ReviewReply  $reviewReply
     * @return void
     */
    public function deleted(ReviewReply $reviewReply)
    {
        //
    }

    /**
     * Handle the review reply "restored" event.
     *
     * @param  \App\ReviewReply  $reviewReply
     * @return void
     */
    public function restored(ReviewReply $reviewReply)
    {
        //
    }

    /**
     * Handle the review reply "force deleted" event.
     *
     * @param  \App\ReviewReply  $reviewReply
     * @return void
     */
    public function forceDeleted(ReviewReply $reviewReply)
    {
        //
    }
}
