<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewReplyTemplate extends Model
{

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $primaryKey = 'review_reply_template_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['template_name','template','is_autoreply_template','target_star_rating','is_deleted'];

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
        return $query->where('is_deleted', '=', config('const.FLG_OFF'));
    }

    public function scopeByTargetStarRating($query, $rate)
    {
        return $query->where('target_star_rating', '=', $rate);
    }

    public function scopeAutoReply($query)
    {
        return $query->where('is_autoreply_template', '=', config('const.FLG_ON'));
    }

    public function scopeManualReply($query)
    {
        return $query->where('is_autoreply_template', '=', config('const.FLG_OFF'));
    }

}