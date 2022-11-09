<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSpecialHoursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('special_hours', function(Blueprint $table)
		{
			$table->increments('special_hour_id');
			$table->integer('location_id')->unsigned()->index('idx_sh_location_id');
			$table->date('gmb_start_day')->nullable();
			$table->time('gmb_start_time')->nullable();
			$table->date('gmb_end_day')->nullable();
			$table->time('gmb_end_time')->nullable();
			$table->tinyInteger('is_closed')->nullable()->default(0)->comment('削除フラグ');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('special_hours');
	}

}
