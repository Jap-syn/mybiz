<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBusinessHoursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('business_hours', function(Blueprint $table)
		{
			$table->foreign('location_id', 'fx_bh_location_id')->references('location_id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('business_hours', function(Blueprint $table)
		{
			$table->dropForeign('fx_bh_location_id');
		});
	}

}
