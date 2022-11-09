<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropEnterpriseAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('enterprise_accounts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('enterprise_accounts', function (Blueprint $table) {
            $table->integer('enterprise_id')->unsigned();
            $table->integer('account_id')->unsigned()->index('fx_ea_account_id_idx');
            $table->integer('create_user_id')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->integer('update_user_id')->nullable();
            $table->dateTime('update_time')->nullable();
            $table->primary(['enterprise_id','account_id']);
            $table->foreign('account_id', 'fx_ea_account_id')->references('account_id')->on('accounts')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('enterprise_id', 'fx_ea_enterprise_id')->references('enterprise_id')->on('enterprises')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
        DB::statement("ALTER TABLE enterprise_accounts COMMENT '運営する系列ブランドを管理するテーブル'");
    }
}
