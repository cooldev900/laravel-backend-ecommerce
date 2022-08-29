<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCheckoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checkout_coms', function (Blueprint $table) {
            $table->text('public_api_key')->default('')->nullable()->change();
            $table->text('secret_api_key')->default('')->nullable()->change();
            $table->text('webhook_secret')->default('')->nullable()->change();
            $table->text('public_api_key_sandbox')->default('')->nullable()->change();
            $table->text('secret_api_key_sandbox')->default('')->nullable()->change();
            $table->text('webhook_secret_sandbox')->default('')->nullable()->change();
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
