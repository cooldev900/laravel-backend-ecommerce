<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPassCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_pass_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(-1)->nullable();
            $table->string('passcode')->default('')->nullable();
            $table->integer('fail_num')->default(0)->nullable();
            $table->string('token')->default('')->nullable();
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
        Schema::dropIfExists('user_pass_codes');
    }
}
