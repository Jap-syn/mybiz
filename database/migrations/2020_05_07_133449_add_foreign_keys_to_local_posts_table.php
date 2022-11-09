<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLocalPostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('local_posts', function(Blueprint $table)
		{
			$table->foreign('location_id', 'fx_lp_location_id')->references('location_id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('local_posts', function(Blueprint $table)
		{
			$table->dropForeign('fx_lp_location_id');
		});
	}

}
