<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReviewsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reviews', function(Blueprint $table)
		{
			$table->increments('review_id');
			$table->integer('location_id')->unsigned()->index('idx_re_location_id');
			$table->string('gmb_account_id', 30)->comment('accounts/{accountId}の{accountId}');
			$table->string('gmb_location_id', 30)->comment('accounts/{accountId}/locations/{location_id}の{location_id}');
			$table->string('gmb_review_id', 200)->comment('accounts/{accountId}/locations/{location_id}/reviews/{reviewId}の{reviewId}');
			$table->text('gmb_reviewer_profile_photo_url', 65535)->nullable();
			$table->string('gmb_reviewer_display_name', 100)->nullable();
			$table->boolean('gmb_reviewer_is_anonymous')->nullable();
			$table->enum('gmb_star_rating', array('STAR_RATING_UNSPECIFIED','ONE','TWO','THREE','FOUR','FIVE'))->nullable();
			$table->text('gmb_comment', 65535)->nullable();
			$table->dateTime('gmb_create_time')->nullable();
			$table->dateTime('gmb_update_time')->nullable();
			$table->text('gmb_review_reply_comment', 65535)->nullable();
			$table->dateTime('gmb_review_reply_update_time')->nullable();
			$table->tinyInteger('is_deleted')->default(0)->comment('削除フラグ');
			$table->enum('sync_type', array('CREATE','PATCH','DELETE'))->nullable()->comment('API連携メソッド');
			$table->enum('sync_status', array('DRAFT','QUEUED','FAILED','CANCEL','SYNCED'))->nullable()->comment('API連携ステータス');
			$table->dateTime('scheduled_sync_time')->nullable()->comment('API連携予定日時');
			$table->dateTime('sync_time')->nullable()->comment('API連携日時');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
			$table->unique(['gmb_account_id','gmb_location_id','gmb_review_id'], 'idx_re_gmb_name');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('reviews');
	}

}
