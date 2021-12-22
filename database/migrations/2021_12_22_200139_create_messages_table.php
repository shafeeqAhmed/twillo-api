<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('sms_uuid')->unique();
            $table->unsignedBigInteger('sender_id')->unsigned();
            $table->unsignedBigInteger('receiver_id')->unsigned();
            $table->string('message_id')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->unsignedBigInteger('chat_user_id')->nullable();
            $table->foreign('chat_user_id')->on('chat_users')->references('id');
             $table->foreign('sender_id')->on('users')->references('id');
            $table->foreign('receiver_id')->on('users')->references('id');
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
        Schema::dropIfExists('messages');
    }
}
