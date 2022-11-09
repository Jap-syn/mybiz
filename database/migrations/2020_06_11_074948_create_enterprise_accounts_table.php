<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEnterpriseAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('enterprise_accounts', function(Blueprint $table)
		{
			$table->integer('enterprise_id')->unsigned();
			$table->integer('account_id')->unsigned()->index('fx_ea_account_id_idx');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->dateTime('update_time')->nullable();
			$table->primary(['enterprise_id','account_id']);
		});
		DB::statement("ALTER TABLE enterprise_accounts COMMENT '運営する系列ブランドを管理するテーブル'");

	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('enterprise_accounts');
	}

}
