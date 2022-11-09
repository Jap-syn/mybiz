<?php
// 修正
namespace App;

use Illuminate\Database\Eloquent\Model;

class LocalPost extends Model
{
    protected $table = 'local_posts';
    protected $primaryKey = 'local_post_id';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function scopeActive($query)
    {
        return $query->where('local_posts.is_deleted', '=', config('const.IS_DELETED.OFF'));
    }
    // 処理中状態
    public function scopeProcessing($query)
    {
        return $query->where('local_posts.is_deleted', '=', config('const.IS_DELETED.PROCESSING'));
    }

    public function scopeQueued($query)
    {
        return $query->where('local_posts.sync_status', '=', config('const.SYNC_STATUS.QUEUED'))
                    ->orWhere('local_posts.sync_status', '=', config('const.SYNC_STATUS.FAILED'));
    }

}
