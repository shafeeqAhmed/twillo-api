<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_links', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_link_uuid')->unique();
            $table->unsignedBigInteger('influencer_id')->unsigned();
            $table->unsignedBigInteger('fanclub_id')->unsigned();
            $table->foreign('influencer_id')->on('users')->references('id');
            $table->foreign('fanclub_id')->on('fan_clubs')->references('id');
            $table->string('link');
            $table->boolean('is_visited')->default(false);
            $table->datetime('visited_date')->nullable()->default(null);
            $table->integer('total_visits')->default(0);
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
        Schema::dropIfExists('message_links');
    }
}
