<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaItem2GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('media_item2_groups')->insert([
            ['media_item2_group_id' => '1', 'account_id' => '1', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_media_key' => '1', 'create_time' => '2020-09-14 21:41:48', 'update_time' => '2020-09-16 12:00:00', ],
            ['media_item2_group_id' => '2', 'account_id' => '1', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_media_key' => '2', 'create_time' => '2020-09-18 09:00:00', 'update_time' => null, ],
        ]);
    }
}
