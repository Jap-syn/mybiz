<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('attributes', function(Blueprint $table)
		{
			$table->foreign('location_id', 'fx_ab_location_id')->references('location_id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('attributes', function(Blueprint $table)
		{
			$table->dropForeign('fx_ab_location_id');
		});
	}

}
