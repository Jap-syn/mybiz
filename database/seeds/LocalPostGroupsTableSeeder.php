<?php

use Illuminate\Database\Seeder;

class LocalPostGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('local_post_groups')->insert([
            ['id' => '1', 'account_id' => '1', 'gmb_account_id' => '1', 'topic_type' => 'STANDARD', 'event_title' => '初夏のキャンペーン開催します', 'is_deleted' => '0', ],
            ['id' => '2', 'account_id' => '2', 'gmb_account_id' => '2', 'topic_type' => 'STANDARD', 'event_title' => '新メニューの紹介', 'is_deleted' => '0', ],
        ]);
    }
}
