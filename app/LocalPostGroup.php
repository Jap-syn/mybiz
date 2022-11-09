<?php
// 修正
namespace App;

use Illuminate\Database\Eloquent\Model;

class LocalPostGroup extends Model
{
    protected $table = 'local_post_groups';

    protected $primaryKey = 'id';
    
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function scopeActive($query)
    {
        return $query->where('local_post_groups.is_deleted', '=', config('const.FLG_OFF'));
    }
}
