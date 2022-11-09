<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('locations', function(Blueprint $table)
		{
			$table->increments('location_id');
			$table->integer('account_id')->unsigned()->index('idx_lo_account_id');
			$table->string('gmb_account_id', 30)->comment('accounts/{accountId}の{accountId}');
			$table->string('gmb_location_id', 30)->comment('accounts/{accountId}/locations/{location_id}の{location_id}');
			$table->string('gmb_language_code', 10)->nullable()->comment('ロケーションの言語');
			$table->string('gmb_store_code', 30)->nullable()->comment('この場所の外部識別子。特定のアカウント内で一意である必要があり');
			$table->string('gmb_location_name', 100)->comment('ロケーション名');
			$table->string('gmb_primary_phone', 16)->nullable()->comment('個々のビジネス拠点にできるだけ直接接続する電話番号');
			$table->string('gmb_additional_phones_1', 16)->nullable()->comment('付加電話番号');
			$table->string('gmb_additional_phones_2', 16)->nullable()->comment('付加電話番号');
			$table->string('gmb_postaladdr_region_code', 10)->comment('organizationInfo.postalAddress  必須。 住所の国/地域のCLDR地域コード');
			$table->string('gmb_postaladdr_language_code', 10)->nullable()->comment('organizationInfo.postalAddress オプション。 このアドレスの言語コード');
			$table->string('gmb_postaladdr_postal_code', 10)->nullable()->comment('organizationInfo.postalAddress オプション。 住所の郵便番号');
			$table->string('gmb_postaladdr_sorting_code', 50)->nullable()->comment('organizationInfo.postalAddress オプション。 追加の、国固有のソートコード');
			$table->string('gmb_postaladdr_admin_area', 10)->nullable()->comment('organizationInfo.postalAddress オプション。 国または地域の住所に使用される最高の行政区画');
			$table->text('gmb_postaladdr_locality', 65535)->nullable()->comment('organizationInfo.postalAddress オプション。 住所の都市/町の部分');
			$table->text('gmb_postaladdr_sublocality', 65535)->nullable()->comment('organizationInfo.postalAddress オプション。 アドレスのサブローカリティ');
			$table->text('gmb_postaladdr_address_lines', 65535)->nullable()->comment('organizationInfo.postalAddress addressLinesの値にはタイプ情報がなく、1つのフィールドに複数の値が含まれる場合がある');
			$table->text('gmb_postaladdr_recipients', 65535)->nullable()->comment('organizationInfo.postalAddress オプション。 アドレスの受信者。複数行の情報が含まれる場合あり');
			$table->string('gmb_postaladdr_organization', 100)->nullable()->comment('organizationInfo.postalAddress オプション。 住所の組織の名前');
			$table->integer('gmb_primary_category_id')->nullable()->comment('この場所が従事するコアビジネスを最もよく表すカテゴリ');
			$table->text('gmb_website_url', 65535)->nullable()->comment('このビジネスのURL');
			$table->enum('gmb_servicearea_business_type', array('BUSINESS_TYPE_UNSPECIFIED','CUSTOMER_LOCATION_ONLY','CUSTOMER_AND_BUSINESS_LOCATION'))->nullable()->comment('サービス提供地域のビジネスのタイプ');
			$table->float('gmb_servicearea_latitude', 9, 6)->nullable()->comment('サービス提供地域の経度');
			$table->float('gmb_servicearea_longitude', 9, 6)->nullable()->comment('サービス提供地域の緯度');
			$table->float('gmb_servicearea_radius_km', 10, 0)->nullable()->comment('サービス提供地域のエリアのキロメートル単位の距離');
			$table->text('gmb_servicearea_placeinfo', 65535)->nullable()->comment('サービス提供地域の場所IDで表されるエリア名とID　最大20件 。複数ある場合は、placeId + nameの組み合わせをカンマ区切りで格納');
			$table->string('gmb_locationkey_pluspage_id', 50)->nullable()->comment('この場所のGoogle+ページID');
			$table->string('gmb_locationkey_place_id', 50)->nullable()->comment('場所の場所ID');
			$table->boolean('gmb_locationkey_explicit_no_place_id')->nullable()->comment('値trueは、未設定の場所IDが意図的なものであることを示す');
			$table->string('gmb_locationkey_request_id', 50)->nullable()->comment('この場所の作成に使用されるrequestId');
			$table->text('gmb_labels', 65535)->nullable()->comment('ビジネスにタグを付けることができるラベル。複数ある場合はカンマ区切りで格納。');
			$table->string('gmb_adwords_adphone', 16)->nullable()->comment('AdWordsに表示される追加情報  場所のメインの電話番号ではなく、AdWordsの住所表示オプションに表示する代替電話番号');
			$table->float('gmb_latlng_latitude', 9, 6)->nullable()->comment('ユーザー指定の緯度');
			$table->float('gmb_latlng_longitude', 9, 6)->nullable()->comment('ユーザー指定の経度');
			$table->enum('gmb_openinfo_status', array('OPEN_FOR_BUSINESS_UNSPECIFIED','OPEN','CLOSED_PERMANENTLY'))->nullable()->comment('場所が現在営業中かどうかを示すフラグ 　＞　ロケーションのステータス');
			$table->boolean('gmb_openinfo_can_reopen')->nullable()->comment('このビジネスが再開できるかどうかを示す');
			$table->string('gmb_openinfo_opening_date', 8)->nullable()->comment('ロケーションが最初に開かれた日付');
			$table->boolean('gmb_state_is_google_updated')->nullable();
			$table->boolean('gmb_state_is_duplicate')->nullable();
			$table->boolean('gmb_state_is_suspended')->nullable();
			$table->boolean('gmb_state_can_update')->nullable();
			$table->boolean('gmb_state_can_delete')->nullable();
			$table->boolean('gmb_state_is_verified')->nullable();
			$table->boolean('gmb_state_needs_reverification')->nullable();
			$table->boolean('gmb_state_is_pending_review')->nullable();
			$table->boolean('gmb_state_is_disabled')->nullable();
			$table->boolean('gmb_state_is_published')->nullable();
			$table->boolean('gmb_state_is_disconnected')->nullable();
			$table->boolean('gmb_state_is_local_post_api_disabled')->nullable();
			$table->boolean('gmb_state_has_pending_edits')->nullable();
			$table->boolean('gmb_state_has_pending_verification')->nullable();
			$table->string('gmb_metadata_duplicate_location_name', 100)->nullable();
			$table->string('gmb_metadata_duplicate_place_id', 50)->nullable();
			$table->enum('gmb_metadata_duplicate_access', array('ACCESS_UNSPECIFIED','ACCESS_UNKNOWN','ALLOWED','INSUFFICIENT'))->nullable();
			$table->text('gmb_metadata_maps_url', 65535)->nullable();
			$table->text('gmb_metadata_new_review_url', 65535)->nullable();
			$table->text('gmb_profile_description', 65535)->nullable()->comment('自分の声での場所の説明');
			$table->string('gmb_relationship_parent_chain', 100)->nullable()->comment('この場所がメンバーとなっているチェーンのリソース名');
			$table->boolean('review_is_autoreplied')->default(false)->comment('クチコミ自動返信フラグ');
			$table->boolean('review_is_notified')->default(true)->comment('クチコミ通知フラグ');
			$table->tinyInteger('is_deleted')->default(0)->comment('削除フラグ');
			$table->enum('sync_type', array('CREATE','PATCH','DELETE'))->nullable()->comment('API連携メソッド');
			$table->enum('sync_status', array('DRAFT','QUEUED','FAILED','CANCEL','SYNCED'))->nullable()->comment('API連携ステータス');
			$table->dateTime('scheduled_sync_time')->nullable()->comment('API連携予定日時');
			$table->dateTime('sync_time')->nullable()->comment('API連携日時');
			$table->integer('create_user_id')->nullable();
			$table->dateTime('create_time')->nullable();
			$table->integer('update_user_id')->nullable();
			$table->dateTime('update_time')->nullable();
			$table->unique(['gmb_account_id','gmb_location_id'], 'idx_lo_gmb_name');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('locations');
	}

}
