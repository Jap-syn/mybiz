<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateReviewAggregatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_aggregates', function (Blueprint $table) {
            $table->increments('review_aggregate_id');
            $table->integer('location_id')->unsigned()->index('idx_ra_location_id');
            $table->string('gmb_account_id', 30)->comment('accounts/{accountId}の{accountId}');
            $table->string('gmb_location_id', 30)->comment('accounts/{accountId}/locations/{location_id}の{location_id');
            $table->decimal('gmb_average_rating', 2, 1)->nullable()->comment('口コミ平均評価');
            $table->integer('gmb_total_review_count')->unsigned()->nullable()->comment('口コミ合計数');
            $table->integer('review_unreplied_count')->unsigned()->nullable()->comment('未返信数');
            $table->integer('create_user_id')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->dateTime('update_time')->nullable();
        });

        DB::statement("ALTER TABLE review_aggregates COMMENT '口コミの集計データを管理するテーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_aggregates');
    }
}
