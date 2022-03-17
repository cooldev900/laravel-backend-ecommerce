<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('location_name')->index();
            $table->unsignedBigInteger('location_order_id')->index();
            $table->unsignedBigInteger('vsf_store_id')->index();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_hub')->default(false);
            $table->boolean('collection')->default(false);
            $table->boolean('fitment')->default(false);
            $table->boolean('delivery')->default(false);
            $table->string('brand')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}