<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationReport extends Model
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $primaryKey = 'location_report_id';

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
        'aggregate_date',
        'create_time',
        'update_time'
    ];

    public function scopeByLocations($query, $locations)
    {
        return $query->whereIn('location_id', $locations);
    }

    public function scopeBetweenAggregateDate($query, $startDate, $endDate)
    {
        return $query->whereBetween('aggregate_date', [$startDate, $endDate]);
    }
}
