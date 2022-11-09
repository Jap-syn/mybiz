<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permissions', function(Blueprint $table)
		{
			$table->increments('permission_id')->comment('権限識別番号');
			$table->string('name', 50)->comment('権限名称');
			$table->string('explanation', 100)->nullable()->comment('説明文');
			$table->string('method', 50)->nullable()->comment('操作内容  ALL, READ, PUT, DELETE　カンマ区切りで複数設定可');
			$table->boolean('is_allow')->nullable()->default(1)->comment('true：allow、　false：deny');
			$table->string('target', 50)->nullable()->comment('操作対象（viewなど）');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
		});
		DB::statement("ALTER TABLE permissions COMMENT '許可または拒否する操作権限を管理するテーブル'");
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permissions');
	}

}
