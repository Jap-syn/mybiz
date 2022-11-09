<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReviewReplyTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('review_reply_templates', function(Blueprint $table)
		{
			$table->increments('review_reply_template_id');
			$table->integer('account_id')->unsigned()->index('idx_rrt_account_id');
			$table->string('template_name', 100)->nullable();
			$table->text('template', 65535)->nullable();
			$table->boolean('is_autoreply_template')->nullable();
			$table->tinyInteger('target_star_rating')->nullable();
			$table->tinyInteger('is_deleted')->default(0);
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('review_reply_templates');
	}

}
