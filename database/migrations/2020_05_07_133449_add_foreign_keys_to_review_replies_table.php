<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToReviewRepliesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('review_replies', function(Blueprint $table)
		{
			$table->foreign('review_id', 'fx_rr_review_id')->references('review_id')->on('reviews')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('review_replies', function(Blueprint $table)
		{
			$table->dropForeign('fx_rr_review_id');
		});
	}

}
