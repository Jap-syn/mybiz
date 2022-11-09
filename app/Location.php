<?php
// 修正
namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'location_id';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


    // 修正
    public function scopeActive($query)
    {
        return $query->where('locations.is_deleted', '=', config('const.IS_DELETED.OFF'));
    }
    
    public function scopeQueued($query)
    {
        return $query->where('locations.sync_status', '=', config('const.SYNC_STATUS.QUEUED'))
                    ->orWhere('locations.sync_status', '=', config('const.SYNC_STATUS.FAILED'));
    }




}
