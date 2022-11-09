<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles', function(Blueprint $table)
		{
			$table->increments('role_id');
			$table->string('name', 50)->nullable()->comment('ロール集合名称');
			$table->string('explanation', 100)->nullable()->comment('説明文');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
		});
		DB::statement("ALTER TABLE roles COMMENT 'ロールを管理するテーブル'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('roles');
	}

}
