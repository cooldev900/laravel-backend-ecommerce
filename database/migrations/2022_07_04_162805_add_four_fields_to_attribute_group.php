<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFourFieldsToAttributeGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attribute_groups', function (Blueprint $table) {
            $table->boolean('product_tool_1')->default(false)->nullable();
            $table->boolean('product_tool_2')->default(false)->nullable();
            $table->boolean('product_tool_3')->default(false)->nullable();
            $table->boolean('product_tool_4')->default(false)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attribute_groups', function (Blueprint $table) {
            //
        });
    }
}
