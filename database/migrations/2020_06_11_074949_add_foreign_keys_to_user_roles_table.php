<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_roles', function(Blueprint $table)
		{
			$table->foreign('account_id', 'fx_urs_account_id')->references('account_id')->on('accounts')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('location_id', 'fx_urs_location_id')->references('location_id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('role_id', 'fx_urs_role_id')->references('role_id')->on('roles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('user_id', 'fx_urs_user_id')->references('user_id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_roles', function(Blueprint $table)
		{
			$table->dropForeign('fx_urs_account_id');
			$table->dropForeign('fx_urs_location_id');
			$table->dropForeign('fx_urs_role_id');
			$table->dropForeign('fx_urs_user_id');
		});
	}

}
