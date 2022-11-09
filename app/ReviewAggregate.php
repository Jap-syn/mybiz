<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewAggregate extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $primaryKey = 'review_aggregate_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $dates = [
        'create_time',
        'update_time'
    ];

    public function scopeByLocations($query, $locations)
    {
        return $query->whereIn('location_id', $locations);
    }

    public function scopeBetweenCreateDate($query, $startDate, $endDate)
    {
        return $query->whereBetween('create_time', [$startDate, $endDate]);
    }
}
