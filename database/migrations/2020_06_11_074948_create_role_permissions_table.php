<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRolePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('role_permissions', function(Blueprint $table)
		{
			$table->integer('role_id')->unsigned();
			$table->integer('permission_id')->unsigned()->index('fx_rp_permission_id_idx');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
			$table->primary(['role_id','permission_id']);
		});
		DB::statement("ALTER TABLE role_permissions COMMENT '複数の操作権限を管理するテーブル　（ロールセットに紐づく操作権限を管理する）'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('role_permissions');
	}

}
