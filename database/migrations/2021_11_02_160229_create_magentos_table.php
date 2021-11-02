<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 500);
            $table->string('consumer_key', 500);
            $table->string('consumer_secret', 500);
            $table->string('token', 500);
            $table->string('token_secret', 500);
            $table->foreignId('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magentos');
    }
}
