<?php

use Illuminate\Database\Seeder;

class ReviewReplyTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('review_reply_templates')->insert(
            [
                [
                    'review_reply_template_id' => '1',
                    'account_id' => '1',
                    'template_name' => '★４つ返信用テンプレート',
                    'template' => '嬉しい評価をありがとうございます！また機会がありましたら、ぜひご利用頂ければ幸いです(^_^)',
                    'is_autoreply_template' => false,
                    'target_star_rating' => '4',
                    'is_deleted' => 0,
                    'create_user_id' => '2001',
                    'create_time' => '2020/06/28 9:09:23',
                    'update_user_id' => '2001',
                    'update_time' => '2020/06/28 9:09:23'
                ],
                [
                    'review_reply_template_id' => '2',
                    'account_id' => '3',
                    'template_name' => '★３つ返信用テンプレート',
                    'template' => 'コメントありがとうございます！より気持ちよく過ごして頂けるよう、これからも精進して参ります。また機会がありましたら、ぜひご利用頂ければ幸いです！',
                    'is_autoreply_template' => false,
                    'target_star_rating' => '3',
                    'is_deleted' => 0,
                    'create_user_id' => '2005',
                    'create_time' => '2020/06/28 9:09:23',
                    'update_user_id' => '2005',
                    'update_time' => '2020/06/28 9:09:23'
                ],
            ]
        );
    }
}
