<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripes', function (Blueprint $table) {
            $table->id();
            $table->string('public_api_key')->nullable()->default('');
            $table->string('secret_api_key')->nullable()->default('');
            $table->string('webhook_secret')->nullable()->default('');
            $table->string('public_api_key_sandbox')->nullable()->default('');
            $table->string('secret_api_key_sandbox')->nullable()->default('');
            $table->string('webhook_secret_sandbox')->nullable()->default('');
            $table->boolean('status')->default(false);
            $table->boolean('sandbox')->default(false)->nullable();
            $table->boolean('manual_capture')->default(false);
            $table->boolean('refund_in_platform')->default(false);
            $table->foreignId('store_view_id')->nullable();
            $table->foreign('store_view_id')->references('id')->on('store_views')->onUpdate('RESTRICT')->onDelete('RESTRICT');
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
        Schema::dropIfExists('stripes');
    }
}
