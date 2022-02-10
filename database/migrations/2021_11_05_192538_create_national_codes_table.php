<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNationalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('national_codes', function (Blueprint $table) {
            $table->string('ID');
            $table->string('Vehicle_Type');
            $table->string('Make');
            $table->string('Model_Group');
            $table->string('undefined');
            $table->string('Body_style');
            $table->string('Fuel');
            $table->string('Trim');
            $table->string('Engine_Size');
            $table->string('Model');
            $table->string('StartYear');
            $table->string('StartMonth');
            $table->string('EndYear');
            $table->string('EndMonth');
            $table->string('TYPImpBegin');
            $table->string('TYPImpEnd');
            $table->string('TYPValvpCyl');
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
        Schema::dropIfExists('national_codes');
    }
}