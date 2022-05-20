<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributesGroupStoreviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_groups_storeview', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('attribute_group_id')->nullable();
            $table->string('store_view')->nullable();
            $table->foreign('attribute_group_id')->references('id')->on('attribute_groups')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_groups_storeview');
    }
}