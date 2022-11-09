<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBusinessHoursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('business_hours', function(Blueprint $table)
		{
			$table->increments('business_hour_id');
			$table->integer('location_id')->unsigned()->index('idx_bh_location_id');
			$table->enum('gmb_open_day', array('DAY_OF_WEEK_UNSPECIFIED','MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY'))->nullable();
			$table->time('gmb_open_time')->nullable();
			$table->enum('gmb_close_day', array('DAY_OF_WEEK_UNSPECIFIED','MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY'))->nullable();
			$table->time('gmb_close_time')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('business_hours');
	}

}
