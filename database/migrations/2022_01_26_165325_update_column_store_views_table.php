<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnStoreViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_views', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable();
            $table->string('payment_provider')->nullable();
            $table->string('api_key_1', 500)->nullable();
            $table->string('api_key_2', 500)->nullable();
            $table->string('payment_additional_1', 500)->nullable();
            $table->string('payment_additional_2', 500)->nullable();
            $table->string('payment_additional_3', 500)->nullable();
            $table->dropUnique('store_views_code_unique');

            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_views', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn('payment_provider');
            $table->dropColumn('api_key_1');
            $table->dropColumn('api_key_2');
            $table->dropColumn('payment_additional_1');
            $table->dropColumn('payment_additional_2');
            $table->dropColumn('payment_additional_3');
        });
    }
}