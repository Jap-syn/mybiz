<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToLocationReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_reports', function (Blueprint $table) {
            $table->unique(['location_id', 'aggregate_date'], 'idx_lr_gmb_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_reports', function (Blueprint $table) {
            $table->dropIndex('idx_lr_gmb_name');
        });
    }
}
