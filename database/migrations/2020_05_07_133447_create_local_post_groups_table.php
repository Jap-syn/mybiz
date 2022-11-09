<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocalPostGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('local_post_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('account_id');
			$table->string('gmb_account_id', 30)->comment('accounts/{accountId}の{accountId}');
			$table->enum('topic_type', array('LOCAL_POST_TOPIC_TYPE_UNSPECIFIED','STANDARD','EVENT','OFFER','ALERT'));
			$table->string('event_title', 200);
			$table->dateTime('event_start_time')->nullable();
			$table->dateTime('event_end_time')->nullable();
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
		Schema::drop('local_post_groups');
	}

}
