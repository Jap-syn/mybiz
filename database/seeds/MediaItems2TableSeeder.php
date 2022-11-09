<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaItems2TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('media_items2')->insert([
            ['media_item2_id' => '1', 'location_id' => '1', 'media_item2_group_id' => '1', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_media_key' => '1', 'sync_status' => 'SYNCED', 'sync_time' => '2020-09-16 12:00:00', ],
            ['media_item2_id' => '2', 'location_id' => '2', 'media_item2_group_id' => '1', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_media_key' => '2', 'sync_status' => 'SYNCED', 'sync_time' => '2020-09-16 12:00:00', ],
            ['media_item2_id' => '3', 'location_id' => '1', 'media_item2_group_id' => '2', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_media_key' => '3', 'sync_status' => 'SYNCED', 'sync_time' => '2020-09-18 09:00:00', ],
            ['media_item2_id' => '4', 'location_id' => '2', 'media_item2_group_id' => '2', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_media_key' => '4', 'sync_status' => 'SYNCED', 'sync_time' => '2020-09-18 09:00:00', ],
        ]);
    }
}
