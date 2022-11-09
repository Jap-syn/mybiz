<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRolePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('role_permissions', function(Blueprint $table)
		{
			$table->foreign('permission_id', 'fx_rp_permission_id')->references('permission_id')->on('permissions')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('role_id', 'fx_rp_role_id')->references('role_id')->on('roles')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('role_permissions', function(Blueprint $table)
		{
			$table->dropForeign('fx_rp_permission_id');
			$table->dropForeign('fx_rp_role_id');
		});
	}

}
