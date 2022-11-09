<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReviewRepliesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('review_replies', function(Blueprint $table)
		{
			$table->increments('review_reply_id');
			$table->integer('review_id')->unsigned()->nullable()->index('idx_rr_review_id');
			$table->text('gmb_comment', 65535)->nullable();
			$table->dateTime('gmb_update_time')->nullable();
			$table->tinyInteger('is_deleted')->default(0)->comment('削除フラグ');
			$table->enum('sync_type', array('CREATE','PATCH','DELETE'))->nullable()->comment('API連携メソッド');
			$table->enum('sync_status', array('DRAFT','QUEUED','FAILED','CANCEL','SYNCED'))->nullable()->comment('API連携ステータス');
			$table->dateTime('scheduled_sync_time')->nullable()->comment('API連携予定日時');
			$table->dateTime('sync_time')->nullable()->comment('API連携日時');
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
		Schema::drop('review_replies');
	}

}
