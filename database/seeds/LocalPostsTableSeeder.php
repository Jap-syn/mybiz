<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalPostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('local_posts')->insert([
            ['local_post_id' => '1', 'location_id' => '1', 'local_post_group_id' => '1', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_local_post_id' => '1', 'gmb_language_code' => '', 'gmb_summary' => '段々と熱くなってきましたね…', 'gmb_action_type_url' => '', 'gmb_create_time' => '', 'gmb_update_time' => '', 'gmb_event_title' => '', 'gmb_search_url' => '', 'gmb_topic_type' => 'STANDARD', 'gmb_offer_coupon_code' => '', 'gmb_offer_redeem_online_url' => '', 'gmb_offer_terms_conditions' => '',],
            ['local_post_id' => '2', 'location_id' => '2', 'local_post_group_id' => '2', 'gmb_account_id' => '2', 'gmb_location_id' => '2', 'gmb_local_post_id' => '2', 'gmb_language_code' => '', 'gmb_summary' => '新メニュー「スタミナ地獄ラーメン」が登場！', 'gmb_action_type_url' => '', 'gmb_create_time' => '', 'gmb_update_time' => '', 'gmb_event_title' => '', 'gmb_search_url' => '', 'gmb_topic_type' => 'STANDARD', 'gmb_offer_coupon_code' => '', 'gmb_offer_redeem_online_url' => '', 'gmb_offer_terms_conditions' => '',],
            ['local_post_id' => '3', 'location_id' => '1', 'local_post_group_id' => '0', 'gmb_account_id' => '1', 'gmb_location_id' => '1', 'gmb_local_post_id' => '3', 'gmb_language_code' => '', 'gmb_summary' => '食欲の秋だ！大収穫祭！！', 'gmb_action_type_url' => '', 'gmb_create_time' => '', 'gmb_update_time' => '', 'gmb_event_title' => '', 'gmb_search_url' => '', 'gmb_topic_type' => 'STANDARD', 'gmb_offer_coupon_code' => '', 'gmb_offer_redeem_online_url' => '', 'gmb_offer_terms_conditions' => '',],
        ]);
    }
}
