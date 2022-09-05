<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPaypalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paypals', function (Blueprint $table) {
            $table->text('client_id')->default('')->nullable()->change();
            $table->text('client_secret')->default('')->nullable()->change();
            $table->text('public_key')->default('')->nullable()->change();
            $table->text('client_id_sandbox')->default('')->nullable()->change();
            $table->text('client_secret_sandbox')->default('')->nullable()->change();
            $table->text('public_key_sandbox')->default('')->nullable()->change();
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
