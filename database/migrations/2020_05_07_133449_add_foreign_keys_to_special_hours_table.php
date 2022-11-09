<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSpecialHoursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('special_hours', function(Blueprint $table)
		{
			$table->foreign('location_id', 'fx_sh_location_id')->references('location_id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('special_hours', function(Blueprint $table)
		{
			$table->dropForeign('fx_sh_location_id');
		});
	}

}
