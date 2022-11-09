<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('location_categories', function (Blueprint $table) {
            $table->increments('location_categories_id');
            $table->integer('location_id');
            $table->integer('category_id');

            $table->integer('create_user_id')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->dateTime('update_time')->nullable();
            
            $table->index('location_id');
            $table->index('category_id');

            $table->foreign('location_id')->references('locations')->on('location_id');
            $table->foreign('category_id')->references('categories')->on('category_id');
        });

        DB::statement("ALTER TABLE location_categories COMMENT '店舗とカテゴリーの関係を管理するテーブル'");
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_categories');
    }
}
