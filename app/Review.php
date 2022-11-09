<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

    // 修正
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


    protected $primaryKey = 'review_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['review_reply_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates   = ['gmb_create_date', 'gmb_update_date'];

    public function reply()
    {
        return $this->hasOne('App\ReviewReply', 'review_reply_id', 'review_reply_id');
    }

    public function scopeActive($query)
    {
        return $query->where('reviews.is_deleted', '=', config('const.IS_DELETED.OFF'));
    }

    public function scopeActiveReplies($query)
    {
        return $query->where('review_replies.is_deleted', '=', config('const.IS_DELETED.OFF'));
    }

    public function scopeByCreateStDate($query, $stDate)
    {
        return $query->where('gmb_create_date', '>=', $stDate);
    }

    public function scopeByCreateEndDate($query, $endDate)
    {
        return $query->where('gmb_create_date', '<=', $endDate);
    }

    public function scopeBetweenCreateDate($query, $stDate, $endDate)
    {
        return $query->whereBetween('gmb_create_date', [$stDate, $endDate]);
    }

    public function scopeByStarRating($query, $rate)
    {
        return $query->where('gmb_star_rating', '=', $rate);
    }

    public function scopeReviewReplyIdIsNull($query)
    {
        return $query->where('review_reply_id', '=', null);
    }

    public function scopeReviewReplyIdIsNotNull($query)
    {
        return $query->where('review_reply_id', '!=', null);
    }

    public function scopeLeftJoinReviewReplies($query)
    {
        return $query->leftJoin('review_replies', 'review_reply_id', '=', 'review_replies.review_reply_id');
    }

}