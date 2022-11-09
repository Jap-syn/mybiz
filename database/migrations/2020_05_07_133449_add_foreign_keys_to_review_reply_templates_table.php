<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToReviewReplyTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('review_reply_templates', function(Blueprint $table)
		{
			$table->foreign('account_id', 'fx_rrt_account_id')->references('account_id')->on('accounts')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('review_reply_templates', function(Blueprint $table)
		{
			$table->dropForeign('fx_rrt_account_id');
		});
	}

}
