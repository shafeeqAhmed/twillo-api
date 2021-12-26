<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('influencer_id');
            $table->foreign('influencer_id')->on('users')->references('id');
            $table->unsignedBigInteger('fan_id')->nullable();
            $table->foreign('fan_id')->on('users')->references('id');
            $table->boolean('is_active')->default(false);
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
        Schema::dropIfExists('fans');
    }
}
