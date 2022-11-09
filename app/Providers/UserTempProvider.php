<?php

namespace App\Providers;

use App\User;
use Illuminate\Auth\EloquentUserProvider;

//仮
class UserTempProvider extends EloquentUserProvider
{
    public function retrieveById($identifier)
    {
        $user = new User();
        $user->user_id = config('const.TEMP_USER_ID');
        return $user;
    }
}
