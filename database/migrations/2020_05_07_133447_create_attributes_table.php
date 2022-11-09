<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attributes', function(Blueprint $table)
		{
			$table->integer('attribute_id')->unsigned()->primary();
			$table->integer('location_id')->unsigned()->index('idx_ab_location_id');
			$table->string('gmb_attributes_attribute_id', 50)->nullable()->comment('この場所の属性ID');
			$table->enum('gmb_attributes_value_type', array('ATTRIBUTE_VALUE_TYPE_UNSPECIFIED','BOOL','ENUM','URL','REPEATED_ENUM'))->nullable()->comment('この属性に含まれる値のタイプ');
			$table->text('gmb_attributes_values', 65535)->nullable()->comment('この属性の値。複数ある場合はカンマ区切りで格納');
			$table->text('gmb_attributes_repeated_set_values', 65535)->nullable()->comment('属性値タイプがREPEATED_ENUMの場合、設定されている列挙値。複数ある場合はカンマ区切りで格納');
			$table->text('gmb_attributes_repeated_unset_values', 65535)->nullable()->comment('設定されていない列挙値。複数ある場合はカンマ区切りで格納');
			$table->text('gmb_attributes_url_values', 65535)->nullable()->comment('属性値タイプがURLの場合、この属性の値。複数ある場合はカンマ区切りで格納');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('attributes');
	}

}
