<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    protected $table = 'media_items';
    protected $primaryKey = 'media_item_id';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function scopeActive($query)
    {
        return $query->where('media_items.is_deleted', '=', config('const.IS_DELETED.OFF'));
    }

    public function scopeQueued($query)
    {
        return $query->where('media_items.sync_status', '=', config('const.SYNC_STATUS.QUEUED'))
                    ->orWhere('media_items.sync_status', '=', config('const.SYNC_STATUS.FAILED'));
    }
}
