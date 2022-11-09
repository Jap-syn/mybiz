<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLocationReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_reports', function (Blueprint $table) {
            $table->increments('location_report_id');
            $table->integer('location_id')->unsigned()->index('idx_lr_location_id');
            $table->dateTime('aggregate_date')->comment('集計日');
            $table->string('gmb_account_id', 30)->comment('accounts/{accountId}の{accountId}');
            $table->string('gmb_location_id', 30)->comment('accounts/{accountId}/locations');
            $table->string('gmb_location_name', 100)->comment('店舗名');
            $table->bigInteger('gmb_queries_direct')->unsigned()->nullable()->comment('店舗直接検索表示回数');
            $table->bigInteger('gmb_queries_indirect')->unsigned()->nullable()->comment('店舗間接検索表示回数');
            $table->bigInteger('gmb_queries_chain')->unsigned()->nullable()->comment('ブランド検索表示回数');
            $table->bigInteger('gmb_views_maps')->unsigned()->nullable()->comment('Googleマップ表示回数');
            $table->bigInteger('gmb_views_search')->unsigned()->nullable()->comment('Google検索表示回数');
            $table->bigInteger('gmb_actions_website')->unsigned()->nullable()->comment('ウェブサイトクリック数');
            $table->bigInteger('gmb_actions_phone')->unsigned()->nullable()->comment('電話番号クリック数');
            $table->bigInteger('gmb_actions_driving_directions')->unsigned()->nullable()->comment('ルート検索リクエスト数');
            $table->bigInteger('gmb_photos_views_merchant')->unsigned()->nullable()->comment('店舗アップロード写真閲覧回数');
            $table->bigInteger('gmb_photos_views_customers')->unsigned()->nullable()->comment('顧客アップロード写真閲覧回数');
            $table->bigInteger('gmb_photos_count_merchant')->unsigned()->nullable()->comment('店舗アップロード写真合計数');
            $table->bigInteger('gmb_photos_count_customers')->unsigned()->nullable()->comment('顧客アップロード写真合計数');
            $table->bigInteger('gmb_local_post_views_search')->unsigned()->nullable()->comment('投稿閲覧回数(Google検索)');
            $table->bigInteger('gmb_local_post_actions_call_to_action')->unsigned()->nullable()->comment('投稿ボタンクリック数(Google)');
            $table->integer('create_user_id')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->dateTime('update_time')->nullable();
        });

        DB::statement("ALTER TABLE location_reports COMMENT '店舗別レポートのデータを管理するテーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_reports');
    }
}
