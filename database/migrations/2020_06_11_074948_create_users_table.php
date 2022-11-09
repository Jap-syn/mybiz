<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('user_id')->comment('ユーザ―識別番号');
			$table->string('name', 50)->comment('担当者名');
			$table->string('email', 50)->unique('email_UNIQUE');
			$table->string('password', 100);
			$table->string('remember_token', 100)->nullable();
			$table->integer('role_id')->unsigned()->index('fx_us_role_id_idx')->comment('ブランド・店舗以外のマイビジ管理画面で操作可能な権限（例えば、権限付与など）');
			$table->integer('enterprise_id')->unsigned()->index('fx_us_enterprise_id_idx')->comment('所属企業識別番号');
			$table->string('department', 50)->nullable()->comment('所属部門名');
			$table->string('phone', 20)->nullable()->comment('電話番号');
			$table->string('notify_line', 200)->nullable()->comment('通知手段 Line');
			$table->string('notify_slack', 200)->nullable()->comment('通知手段 Slack');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
		});
		DB::statement("ALTER TABLE users COMMENT '利用者を管理するテーブル';");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
