<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('print_label')->nullable();
            $table->string('api_url')->nullable();
            $table->string('api_token', 500)->nullable();
            $table->string('api_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('print_label');
            $table->dropColumn('api_url');
            $table->dropColumn('api_token');
            $table->dropColumn('api_user');
        });
    }
}