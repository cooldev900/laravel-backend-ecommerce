<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCybersourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cybersources', function (Blueprint $table) {
            $table->text('merchant_id')->default('')->nullable()->change();
            $table->text('key')->default('')->nullable()->change();
            $table->text('shared_secret_key')->default('')->nullable()->change();
            $table->text('merchant_id_sandbox')->default('')->nullable()->change();
            $table->text('key_sandbox')->default('')->nullable()->change();
            $table->text('shared_secret_key_sandbox')->default('')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
