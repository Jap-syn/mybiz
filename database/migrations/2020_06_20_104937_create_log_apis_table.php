<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLogApisTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log_apis', function(Blueprint $table)
		{
			$table->increments('id')->comment('識別子');
			$table->tinyInteger('kubun')->default(0)->comment('バッチ区分　０：更新バッチ、1:取り込みバッチ');
			$table->tinyInteger('proc_exit')->default(0)->comment('処理結果　0:正常終了、-1:異常終了');
			$table->string('class_function', 100)->comment('処理クラス.関数名');
			$table->text('detail')->nullable()->comment('処理結果の詳細');
			$table->text('exception')->nullable()->comment('例外の詳細');
			$table->dateTime('started_at')->nullable()->comment('処理開始日時');
			$table->dateTime('ended_at')->nullable()->comment('処理終了日時');
			$table->dateTime('created_at')->nullable()->comment('作成日時');
			$table->dateTime('updated_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('log_apis');
	}

}
