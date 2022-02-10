<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('store_view_id');
            $table->foreignId('scope_id');
            $table->foreignId('role_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('store_view_id')->references('id')->on('store_views')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('scope_id')->references('id')->on('scopes')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_permissions');
    }
}
