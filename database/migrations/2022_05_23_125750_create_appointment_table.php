<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->string('customer')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('order_id')->nullable();
            $table->boolean('booked_online')->nullable();
            $table->string('duration')->nullable();
            $table->string('note')->nullable();
            $table->boolean('internal_booking')->nullable();
            $table->foreignId('technician_id')->nullable();
            $table->foreignId('slot_id')->nullable();
            $table->foreign('technician_id')->references('id')->on('technicians')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('slot_id')->references('id')->on('slots')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
