<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEnterpriseAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('enterprise_accounts', function(Blueprint $table)
		{
			$table->foreign('account_id', 'fx_ea_account_id')->references('account_id')->on('accounts')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('enterprise_id', 'fx_ea_enterprise_id')->references('enterprise_id')->on('enterprises')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('enterprise_accounts', function(Blueprint $table)
		{
			$table->dropForeign('fx_ea_account_id');
			$table->dropForeign('fx_ea_enterprise_id');
		});
	}

}
