<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewReply extends Model
{

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $primaryKey = 'review_reply_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['gmb_comment','is_deleted'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates   = ['create_time', 'update_time'];

    protected $attributes = [
        'is_deleted' => 0,
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', '=', config('const.IS_DELETED.OFF'));
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

    // 修正
    public function scopeQueued($query)
    {
        return $query->where('review_replies.sync_status', '=', config('const.SYNC_STATUS.QUEUED'))
                    ->orWhere('review_replies.sync_status', '=', config('const.SYNC_STATUS.FAILED'));
    }

}