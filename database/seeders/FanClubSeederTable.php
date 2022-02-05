<?php

namespace Database\Seeders;

use App\Models\FanClub;
use Illuminate\Database\Seeder;

class FanClubSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FanClub::factory()
            ->count(300)
            ->create();
    }
}
