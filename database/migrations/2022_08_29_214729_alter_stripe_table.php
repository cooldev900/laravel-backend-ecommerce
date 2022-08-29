<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStripeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stripes', function (Blueprint $table) {
            $table->text('public_api_key')->nullable()->default('')->change();
            $table->text('secret_api_key')->nullable()->default('')->change();
            $table->text('webhook_secret')->nullable()->default('')->change();
            $table->text('public_api_key_sandbox')->nullable()->default('')->change();
            $table->text('secret_api_key_sandbox')->nullable()->default('')->change();
            $table->text('webhook_secret_sandbox')->nullable()->default('')->change();
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
