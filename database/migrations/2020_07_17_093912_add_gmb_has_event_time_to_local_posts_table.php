<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGmbHasEventTimeToLocalPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('local_posts', function (Blueprint $table) {
            $table->boolean('gmb_has_event_time')->default(false)->comment('イベントの開始/終了時刻を設定するフラグ true:時刻あり false:時刻なし');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('local_posts', function (Blueprint $table) {
            $table->dropColumn('gmb_has_event_time');
        });
    }
}
