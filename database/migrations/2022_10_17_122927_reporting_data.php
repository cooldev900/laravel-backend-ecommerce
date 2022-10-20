<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReportingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reporting_data', function (Blueprint $table) {
            $table->id();
            $table->date('order_date')->nullable();
            $table->integer('order_number')->nullable();
            $table->unsignedInteger('store_id')->nullable();
            $table->unsignedInteger('client_id')->nullable();
            $table->integer('value')->nullable();
            $table->timestamps();
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
