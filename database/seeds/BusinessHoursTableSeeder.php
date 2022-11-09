<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessHoursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('business_hours')->insert([
            ['business_hour_id' => '1', 'location_id' => '1', 'gmb_open_day' => 'SUNDAY', 'gmb_open_time' => '0:00:00', 'gmb_close_day' => 'SUNDAY', 'gmb_close_time' => '0:00:00', ],
            ['business_hour_id' => '2', 'location_id' => '2', 'gmb_open_day' => 'MONDAY', 'gmb_open_time' => '1:00:00', 'gmb_close_day' => 'MONDAY', 'gmb_close_time' => '21:00:00', ],
            ['business_hour_id' => '3', 'location_id' => '3', 'gmb_open_day' => 'TUESDAY', 'gmb_open_time' => '2:00:00', 'gmb_close_day' => 'TUESDAY', 'gmb_close_time' => '22:00:00', ],
            ['business_hour_id' => '4', 'location_id' => '4', 'gmb_open_day' => 'WEDNESDAY', 'gmb_open_time' => '3:00:00', 'gmb_close_day' => 'WEDNESDAY', 'gmb_close_time' => '23:00:00', ],
            ['business_hour_id' => '6', 'location_id' => '5', 'gmb_open_day' => 'FRIDAY', 'gmb_open_time' => '5:00:00', 'gmb_close_day' => 'FRIDAY', 'gmb_close_time' => '23:00:00', ],
            ['business_hour_id' => '7', 'location_id' => '6', 'gmb_open_day' => 'SATURDAY', 'gmb_open_time' => '6:00:00', 'gmb_close_day' => 'SATURDAY', 'gmb_close_time' => '23:00:00', ],
        ]);
    }
}
