<?php
// 修正
namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationLocalPost extends Model
{
    protected $table = 'location_local_posts';

    protected $primaryKey = ['local_post_id', 'location_id'];
    protected $fillable = ['local_post_id', 'location_id'];

    public $incrementing = false;
    
    const CREATED_AT = 'create_time';
}
