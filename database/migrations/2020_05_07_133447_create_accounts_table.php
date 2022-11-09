<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('accounts', function(Blueprint $table)
		{
			$table->increments('account_id');
			$table->string('gmb_account_id', 30)->unique('idx_ac_gmb_name')->comment('accounts/{accountId}の{accountId}');
			$table->string('gmb_account_name', 100)->comment('accountName　アカウントの名前');
			$table->enum('gmb_account_type', array('ACCOUNT_TYPE_UNSPECIFIED','PERSONAL','LOCATION_GROUP','USER_GROUP','ORGANIZATION'))->nullable()->comment('このアカウントのaccounttype');
			$table->enum('gmb_account_role', array('ACCOUNT_ROLE_UNSPECIFIED','OWNER','CO_OWNER','MANAGER','COMMUNITY_MANAGER'))->nullable()->comment('発信者がこのアカウントに対して持っているAccountRole');
			$table->enum('gmb_account_state', array('ACCOUNT_STATUS_UNSPECIFIED','VERIFIED','UNVERIFIED','VERIFICATION_REQUESTED'))->nullable()->comment('このアカウントのAccountState');
			$table->text('gmb_profile_photo_url', 65535)->nullable()->comment('アカウントのプロフィール写真');
			$table->string('gmb_account_number', 50)->nullable()->comment('プロビジョニングされている場合、アカウント参照番号');
			$table->enum('gmb_permission_level', array('PERMISSION_LEVEL_UNSPECIFIED','OWNER_LEVEL','MEMBER_LEVEL'))->nullable()->comment('このアカウントに対して呼び出し元が持っているPermissionLevel');
			$table->string('gmb_orginfo_registered_domain', 100)->nullable()->comment('organizationInfo  アカウントの登録済みドメイン');
			$table->string('gmb_orginfo_postaladdr_region_code', 10)->comment('organizationInfo.postalAddress  必須。 住所の国/地域のCLDR地域コード');
			$table->string('gmb_orginfo_postaladdr_language_code', 10)->nullable()->comment('organizationInfo.postalAddress オプション。 このアドレスの言語コード');
			$table->string('gmb_orginfo_postaladdr_postal_code', 10)->nullable()->comment('organizationInfo.postalAddress オプション。 住所の郵便番号');
			$table->string('gmb_orginfo_postaladdr_sorting_code', 50)->nullable()->comment('organizationInfo.postalAddress オプション。 追加の、国固有のソートコード');
			$table->string('gmb_orginfo_postaladdr_admin_area', 10)->nullable()->comment('organizationInfo.postalAddress オプション。 国または地域の住所に使用される最高の行政区画。東京都');
			$table->text('gmb_orginfo_postaladdr_locality', 65535)->nullable()->comment('organizationInfo.postalAddress オプション。 住所の都市/町の部分');
			$table->text('gmb_orginfo_postaladdr_sublocality', 65535)->nullable()->comment('organizationInfo.postalAddress オプション。 アドレスのサブローカリティ');
			$table->text('gmb_orginfo_postaladdr_address_lines', 65535)->nullable()->comment('organizationInfo.postalAddress addressLinesの値にはタイプ情報がなく、1つのフィールドに複数の値が含まれる場合がある');
			$table->text('gmb_orginfo_postaladdr_recipients', 65535)->nullable()->comment('organizationInfo.postalAddress オプション。 アドレスの受信者。複数行の情報が含まれる場合あり');
			$table->string('gmb_orginfo_postaladdr_organization', 100)->nullable()->comment('organizationInfo.postalAddress オプション。 住所の組織の名前');
			$table->string('gmb_orginfo_phone_number', 20)->nullable()->comment('organizationInfo 組織の連絡先番号');
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
		Schema::drop('accounts');
	}

}
