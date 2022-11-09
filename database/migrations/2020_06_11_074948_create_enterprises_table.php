<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnterprisesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('enterprises', function(Blueprint $table)
		{
			$table->increments('enterprise_id')->comment('所属企業識別番号');
			$table->string('name', 50)->comment('企業名');
			$table->string('postal_code', 7)->nullable();
			$table->string('prefectures', 10)->nullable()->comment('都道府県');
			$table->string('address1', 100)->nullable()->comment('住所1');
			$table->string('address2', 100)->nullable()->comment('住所2');
			$table->text('note')->nullable()->comment('企業メモ');
			$table->text('signature')->nullable()->comment('クチコミ通知メールの署名');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
		});
		DB::statement("ALTER TABLE enterprises COMMENT 'ブランドを運営する企業を管理するテーブル'");

	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('enterprises');
	}

}
