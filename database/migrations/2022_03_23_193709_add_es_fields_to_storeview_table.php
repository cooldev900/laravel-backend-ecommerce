<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEsFieldsToStoreviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_views', function (Blueprint $table) {
            $table->string('es_url')->nullable();
            $table->string('es_username')->nullable();
            $table->string('es_password')->nullable();
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
            $table->dropColumn('es_url');
            $table->dropColumn('es_username');
            $table->dropColumn('es_password');
        });
    }
}
