<?php
// 修正
namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'account_id';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function scopeActive($query)
    {
        return $query->where('accounts.is_deleted', '=', config('const.IS_DELETED.OFF'));
    }




}
