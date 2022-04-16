
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBlockColumnToFanClubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fan_clubs', function (Blueprint $table) {
            $table->boolean('is_blocked')->after('is_active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fan_clubs', function (Blueprint $table) {
            $table->dropColumn('is_blocked');
        });
    }
}
