<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocalPostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('local_posts', function(Blueprint $table)
		{
			$table->increments('local_post_id');
			$table->integer('location_id')->unsigned()->index('idx_lp_location_id');
			$table->integer('local_post_group_id')->default(0)->comment('複数店舗に対して、同じ内容で投稿したことを判別するため投稿グループ識別子');
			$table->string('gmb_account_id', 30)->comment('accounts/{accountId}の{accountId}');
			$table->string('gmb_location_id', 30)->comment('accounts/{accountId}/locations/{location_id}の{location_id}');
			$table->string('gmb_local_post_id', 30)->comment('accounts/{accountId}/locations/{location_id}/localPosts/{local_post_id}の{local_post_id}');
			$table->string('gmb_language_code', 10)->nullable()->comment('ローカル投稿の言語');
			$table->text('gmb_summary', 16777215)->nullable()->comment('ローカル投稿の説明/本文');
			$table->enum('gmb_action_type', array('ACTION_TYPE_UNSPECIFIED','BOOK','ORDER','SHOP','LEARN_MORE','SIGN_UP','GET_OFFER','CALL'))->nullable()->comment('実行されるアクションのタイプ');
			$table->text('gmb_action_type_url', 16777215)->nullable()->comment('ユーザーがクリック時にリダイレクトされるURL');
			$table->string('gmb_create_time', 50)->nullable();
			$table->string('gmb_update_time', 50)->nullable();
			$table->string('gmb_event_title', 200)->nullable()->comment('イベントの名前');
			$table->dateTime('gmb_event_start_time')->nullable();
			$table->dateTime('gmb_event_end_time')->nullable();
			$table->enum('gmb_local_post_state', array('LOCAL_POST_STATE_UNSPECIFIED','REJECTED','LIVE','PROCESSING'))->nullable()->comment('投稿の状態');
			$table->text('gmb_search_url', 16777215)->nullable()->comment('Google検索のローカル投稿へのリンク');
			$table->enum('gmb_topic_type', array('LOCAL_POST_TOPIC_TYPE_UNSPECIFIED','STANDARD','EVENT','OFFER','ALERT'))->nullable()->comment('ローカル投稿のトピックタイプ');
			$table->enum('gmb_alert_type', array('ALERT_TYPE_UNSPECIFIED','COVID_19'))->nullable()->comment('Alerts related to the 2019 Coronavirus Disease pandemic. ');
			$table->string('gmb_offer_coupon_code', 100)->nullable()->comment('オプション。 ストアまたはオンラインで使用可能なコード');
			$table->text('gmb_offer_redeem_online_url', 16777215)->nullable()->comment('オプション。 クーポンを利用するためのオンラインリンク');
			$table->text('gmb_offer_terms_conditions', 16777215)->nullable()->comment('オプション。 利用規約');
			$table->tinyInteger('is_deleted')->default(0)->comment('削除フラグ');
			$table->enum('sync_type', array('CREATE','PATCH','DELETE'))->nullable()->comment('API連携メソッド');
			$table->enum('sync_status', array('DRAFT','QUEUED','FAILED','CANCEL','SYNCED'))->nullable()->comment('API連携ステータス');
			$table->dateTime('scheduled_sync_time')->nullable()->comment('API連携予定日時');
			$table->dateTime('sync_time')->nullable()->comment('API連携日時');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
			$table->index(['gmb_account_id','gmb_location_id','gmb_local_post_id'], 'idx_lp_gmb_name');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('local_posts');
	}

}
