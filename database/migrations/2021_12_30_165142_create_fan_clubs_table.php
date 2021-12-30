<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFanClubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fan_clubs', function (Blueprint $table) {
            $table->id();
            $table->uuid('fan_uuid')->unique();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')->on('users')->references('id');
            $table->string('local_number')->nullable();
            $table->unsignedBigInteger('fan_id')->default(0);
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
        Schema::dropIfExists('fan_clubs');
    }
}
