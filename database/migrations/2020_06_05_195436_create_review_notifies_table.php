<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewNotifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_notifies', function (Blueprint $table) {
            $table->increments('review_notify_id');
            $table->integer('location_id')->unsigned()->index('idx_rn_location_id');
            $table->string('name')->nullable()->comment('担当者名');
            $table->string('position')->nullable()->comment('役職名');
            $table->string('email')->nullable()->comment('通知メールアドレス');
            $table->integer('create_user_id')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->dateTime('update_time')->nullable();
        });

        DB::statement("ALTER TABLE review_notifies COMMENT '口コミ通知を管理するテーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('review_notifies');
    }
}
