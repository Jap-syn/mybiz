<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_roles', function(Blueprint $table)
		{
			$table->increments('user_role_id');
			$table->integer('user_id')->unsigned()->index('fx_urs_user_id_idx');
			$table->integer('account_id')->unsigned()->index('fx_urs_account_id_idx')->comment('アクセスできるブランド');
			$table->integer('location_id')->unsigned()->index('fx_urs_location_id_idx')->comment('アクセスできる店舗　（0値は全店舗を意味）');
			$table->integer('role_id')->unsigned()->index('fx_urs_role_id_idx')->comment('アクセスできるブランド・店舗に対する操作可能な権限');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
		});
		DB::statement("ALTER TABLE user_roles COMMENT '利用者がアクセスできるブランド・店舗に対する操作権限を管理するテーブル'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_roles');
	}

}
