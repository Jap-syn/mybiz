<?php

use Illuminate\Database\Seeder;

class ReviewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('reviews')->insert([
            ['review_id' => '1', 'location_id' => '1', 'gmb_account_id' => '001', 'gmb_location_id' => '001', 'gmb_review_id' => '1', 'gmb_reviewer_profile_photo_url' => '', 'gmb_reviewer_display_name' => 'Yamada Saan', 'gmb_star_rating' => 'FOUR', 'gmb_comment' => 'とても美味しくいただきました☺', 'gmb_create_time' => '2020-05-01 10:38:09', 'gmb_update_time' => '2020-05-01 10:38:09', 'gmb_review_reply_comment' => '', 'is_deleted' => 0, 'sync_status' => 'SYNCED', ],
            ['review_id' => '2', 'location_id' => '2', 'gmb_account_id' => '001', 'gmb_location_id' => '001', 'gmb_review_id' => '2', 'gmb_reviewer_profile_photo_url' => '', 'gmb_reviewer_display_name' => '田中安兵衛', 'gmb_star_rating' => 'THREE', 'gmb_comment' => '割と好みの味でした', 'gmb_create_time' => '2020-05-19 13:11:33', 'gmb_update_time' => '2020-05-19 13:11:33', 'gmb_review_reply_comment' => '', 'is_deleted' => 0, 'sync_status' => 'SYNCED', ],
            ['review_id' => '3', 'location_id' => '3', 'gmb_account_id' => '001', 'gmb_location_id' => '001', 'gmb_review_id' => '3', 'gmb_reviewer_profile_photo_url' => '', 'gmb_reviewer_display_name' => 'John Doe', 'gmb_star_rating' => 'TWO', 'gmb_comment' => 'soso...', 'gmb_create_time' => '2020-05-25 20:19:44', 'gmb_update_time' => '2020-05-25 20:19:44', 'gmb_review_reply_comment' => '', 'is_deleted' => 0, 'sync_status' => 'SYNCED', ],
            ['review_id' => '4', 'location_id' => '4', 'gmb_account_id' => '002', 'gmb_location_id' => '002', 'gmb_review_id' => '4', 'gmb_reviewer_profile_photo_url' => '', 'gmb_reviewer_display_name' => 'テスト太郎', 'gmb_star_rating' => 'FIVE', 'gmb_comment' => '', 'gmb_create_time' => '2020-05-25 20:19:44', 'gmb_update_time' => '2020-05-25 20:19:44', 'gmb_review_reply_comment' => '', 'is_deleted' => 0, 'sync_status' => 'SYNCED', ],
            ['review_id' => '5', 'location_id' => '2', 'gmb_account_id' => '001', 'gmb_location_id' => '001', 'gmb_review_id' => '5', 'gmb_reviewer_profile_photo_url' => '', 'gmb_reviewer_display_name' => 'テスト次郎', 'gmb_star_rating' => 'ONE', 'gmb_comment' => 'もう少し濃い味が良かったと思います。
            今後の改善に期待しつつ、また行きたいと思います。', 'gmb_create_time' => '2020-06-25 20:19:44', 'gmb_update_time' => '2020-06-25 20:19:44', 'gmb_review_reply_comment' => '', 'is_deleted' => 0, 'sync_status' => 'SYNCED', ],
            ['review_id' => '6', 'location_id' => '5', 'gmb_account_id' => '002', 'gmb_location_id' => '002', 'gmb_review_id' => '6', 'gmb_reviewer_profile_photo_url' => '', 'gmb_reviewer_display_name' => 'テスト三郎', 'gmb_star_rating' => 'TWO', 'gmb_comment' => '特になし', 'gmb_create_time' => '2020-07-01 20:19:44', 'gmb_update_time' => '2020-07-01 20:19:44', 'gmb_review_reply_comment' => '', 'is_deleted' => 0, 'sync_status' => 'SYNCED', ],
            ['review_id' => '7', 'location_id' => '6', 'gmb_account_id' => '002', 'gmb_location_id' => '002', 'gmb_review_id' => '7', 'gmb_reviewer_profile_photo_url' => '', 'gmb_reviewer_display_name' => 'テスト五朗', 'gmb_star_rating' => 'THREE', 'gmb_comment' => 'まあまあかな、、、', 'gmb_create_time' => '2020-07-02 20:19:44', 'gmb_update_time' => '2020-07-02 20:19:44', 'gmb_review_reply_comment' => '', 'is_deleted' => 0, 'sync_status' => 'SYNCED', ],
        ]);
    }
}
