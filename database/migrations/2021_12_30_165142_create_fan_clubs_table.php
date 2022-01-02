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
            $table->uuid('fan_club_uuid')->unique();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')->on('users')->references('id');
            $table->string('local_number');
            $table->unsignedBigInteger('fan_id')->nullable()->comment('fan id has association with users table');
           // $table->foreign('fan_id')->on('users')->references('id');
            $table->uuid('temp_id')->nullable();
            $table->dateTime('temp_id_date_time')->nullable();
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
        Schema::dropIfExists('fan_clubs');
    }
}
