<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewAggregatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('review_aggregates')->insert([
            ['review_aggregate_id' => '1', 'location_id' => '1', 'gmb_account_id' => '002', 'gmb_location_id' => '001', 'gmb_average_rating' => '4.5', 'gmb_total_review_count' => '333', ],
            ['review_aggregate_id' => '2', 'location_id' => '2', 'gmb_account_id' => '002', 'gmb_location_id' => '002', 'gmb_average_rating' => '4.8', 'gmb_total_review_count' => '555', ],
            ['review_aggregate_id' => '3', 'location_id' => '3', 'gmb_account_id' => '002', 'gmb_location_id' => '003', 'gmb_average_rating' => '4.0', 'gmb_total_review_count' => '222', ],
            ['review_aggregate_id' => '4', 'location_id' => '4', 'gmb_account_id' => '003', 'gmb_location_id' => '004', 'gmb_average_rating' => '4.1', 'gmb_total_review_count' => '666', ],
            ['review_aggregate_id' => '5', 'location_id' => '5', 'gmb_account_id' => '003', 'gmb_location_id' => '005', 'gmb_average_rating' => '3.5', 'gmb_total_review_count' => '444', ],
            ['review_aggregate_id' => '6', 'location_id' => '6', 'gmb_account_id' => '003', 'gmb_location_id' => '006', 'gmb_average_rating' => '3.3', 'gmb_total_review_count' => '111', ],
        ]);
    }
}
