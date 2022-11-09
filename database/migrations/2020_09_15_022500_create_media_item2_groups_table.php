<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaItem2GroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_item2_groups', function(Blueprint $table)
        {
            $table->increments('media_item2_group_id');
            $table->integer('account_id')->unsigned()->index('idx_mi2g_account_id');
            $table->string('gmb_account_id', 30)->comment('accounts/{accountId}の{accountId}');
            $table->string('gmb_location_id', 30)->comment('accounts/{accountId}/locations/{location_id}の{location_id}');
            $table->string('gmb_media_key', 100)->comment('accounts/{accountId}/locations/{location_id}/media/{mediaKey}の{mediaKey}');
            $table->enum('gmb_media_format', array('MEDIA_FORMAT_UNSPECIFIED','PHOTO','VIDEO'))->nullable()->comment('メディアアイテムの形式');
            $table->enum('gmb_location_association_category', array('CATEGORY_UNSPECIFIED','COVER','PROFILE','LOGO','EXTERIOR','INTERIOR','PRODUCT','AT_WORK','FOOD_AND_DRINK','MENU','COMMON_AREA','ROOMS','TEAMS','ADDITIONAL'))->index('idx_mi2g_gmb_location_association_category')->comment('この場所の写真が属するカテゴリ');
            $table->string('gmb_location_association_price_list_item_id', 100)->nullable()->comment('この場所の写真が関連付けられている価格表アイテムのID');
            $table->text('gmb_google_url', 65535)->nullable()->comment('このメディアアイテムのGoogleがホストするURL');
            $table->text('gmb_thumbnail_url', 65535)->nullable()->comment('このメディアアイテムのサムネイル画像のURL');
            $table->string('gmb_create_time', 50)->nullable();
            $table->integer('gmb_dimentions_width_pixels')->unsigned()->nullable();
            $table->integer('gmb_dimentions_height_pixels')->unsigned()->nullable();
            $table->integer('gmb_insights_view_count')->unsigned()->nullable()->comment('メディアアイテムのインサイトと統計');
            $table->string('gmb_attribution_profile_name', 100)->nullable()->comment('メディアアイテムの属性となるユーザー名');
            $table->text('gmb_attribution_profile_photo_url', 65535)->nullable()->comment('帰属するユーザーのプロフィール写真のサムネイルのURL');
            $table->text('gmb_attribution_takedown_url', 65535)->nullable()->comment('不適切な場合にメディアアイテムを報告できる削除ページのURL');
            $table->text('gmb_atttribution_profile_url', 65535)->nullable()->comment('属性ユーザーのGoogleマップのプロフィールページのURL');
            $table->text('gmb_description', 65535)->nullable()->comment('このメディアアイテムの説明');
            $table->text('gmb_source_url', 65535)->nullable()->comment('メディアアイテムを取得できるパブリックにアクセス可能なURL');
            $table->text('gmb_data_ref_resource_name', 65535)->nullable()->comment('My Business APIを介してアップロードされたMediaItemの写真バイナリデータへの参照');
            $table->text('s3_object_url', 65535)->nullable();
            $table->tinyInteger('is_deleted')->default(0)->comment('削除フラグ');
            $table->integer('create_user_id')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->dateTime('update_time')->nullable();
            $table->index(['gmb_account_id','gmb_location_id','gmb_media_key'], 'idx_mi2g_gmb_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('media_item2_groups');
    }
}
