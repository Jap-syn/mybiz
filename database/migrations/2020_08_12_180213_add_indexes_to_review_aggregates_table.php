<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToReviewAggregatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('review_aggregates', function (Blueprint $table) {
            $table->unique(['location_id'], 'idx_ra_gmb_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('review_aggregates', function (Blueprint $table) {
            $table->dropIndex('idx_ra_gmb_name');
        });
    }
}
