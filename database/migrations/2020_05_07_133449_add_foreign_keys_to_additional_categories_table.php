<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAdditionalCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('additional_categories', function(Blueprint $table)
		{
			$table->foreign('category_id', 'fx_ad_category_id')->references('category_id')->on('categories')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('location_id', 'fx_ad_location_id')->references('location_id')->on('locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('additional_categories', function(Blueprint $table)
		{
			$table->dropForeign('fx_ad_category_id');
			$table->dropForeign('fx_ad_location_id');
		});
	}

}
