<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            //['role_id' => 0, 'name' => '一般ユーザー'],
            ['role_id' => 1, 'name' => 'システム管理者'],
            ['role_id' => 10, 'name' => '投稿管理編集者・クチコミ管理編集者'],
            ['role_id' => 11, 'name' => '投稿管理編集者・クチコミ管理閲覧者'],
            ['role_id' => 12, 'name' => '投稿管理編集者'],
            ['role_id' => 13, 'name' => '投稿管理閲覧者・クチコミ管理編集者'],
            ['role_id' => 14, 'name' => '投稿管理閲覧者・クチコミ管理閲覧者'],
            ['role_id' => 15, 'name' => '投稿管理閲覧者'],
            ['role_id' => 16, 'name' => 'クチコミ管理編集者'],
            ['role_id' => 17, 'name' => 'クチコミ管理閲覧者'],
            ['role_id' => 18, 'name' => '投稿管理・クチコミ管理アクセス権限無し'],
            ['role_id' => 21, 'name' => '企業管理ユーザー'],
            ['role_id' => 31, 'name' => 'ブランドマネージャー'],
            ['role_id' => 41, 'name' => '編集者'],
            ['role_id' => 91, 'name' => '閲覧者'],
        ]);
    }
}
