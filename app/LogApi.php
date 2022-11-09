<?php
// 修正
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogApi extends Model
{
    protected $table = 'log_apis';
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
}
