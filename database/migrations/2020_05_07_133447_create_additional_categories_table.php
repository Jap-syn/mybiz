<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdditionalCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('additional_categories', function(Blueprint $table)
		{
			$table->integer('location_id')->unsigned()->index('idx_ad_location_id');
			$table->integer('category_id')->unsigned()->index('idx_ad_category_id');
			$table->primary(['location_id','category_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('additional_categories');
	}

}
