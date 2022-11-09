<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->foreign('enterprise_id', 'fx_us_enterprise_id')->references('enterprise_id')->on('enterprises')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('role_id', 'fx_us_role_id')->references('role_id')->on('roles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('fx_us_enterprise_id');
			$table->dropForeign('fx_us_role_id');
		});
	}

}
