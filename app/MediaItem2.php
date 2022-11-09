<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MediaItem2 extends Model
{
    protected $table = 'media_items2';
    protected $primaryKey = 'media_item2_id';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function scopeActive($query)
    {
        return $query->where('media_items2.is_deleted', '=', config('const.IS_DELETED.OFF'));
    }

    public function scopeQueued($query)
    {
        return $query->where('media_items2.sync_status', '=', config('const.SYNC_STATUS.QUEUED'))
                    ->orWhere('media_items2.sync_status', '=', config('const.SYNC_STATUS.FAILED'));
    }

}
