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
            $table->uuid('fan_uuid')->unique();
            $table->string('fname')->nullable();
            $table->string('lname')->nullable();
            $table->string('email')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('phone_no');
            $table->string('city')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other','Non-Binary'])->nullable();
            $table->date('dob')->nullable();
            $table->text('twitter')->nullable();
            $table->text('instagram')->nullable();
            $table->text('ticktok')->nullable();
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
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
