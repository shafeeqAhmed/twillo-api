<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('broadcast_message', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->on('users')->efference();
            $table->string('message');
            $table->enum('type', ['schedule', 'direct'])->default('direct');
            $table->text('filters')->nullable();
            $table->dateTime('scheduled_at_local_time')->nullable();
            $table->dateTime('scheduled_at_stander_time')->nullable();

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
        Schema::dropIfExists('broadcast_message');
    }
}
