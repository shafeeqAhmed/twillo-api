<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToMessageLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_links', function (Blueprint $table) {
            $table->foreignId('broadcast_id')->after('fanclub_id')->nullable()->on('broadcast_message')->reference('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_links', function (Blueprint $table) {
            $table->dropForeign('broadcast_id');
            $table->dropColumn('broadcast_id');
        });
    }
}
