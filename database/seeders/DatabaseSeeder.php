<?php

namespace Database\Seeders;

use App\Models\FanClub;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            FanClubSeederTable::class,
//            FanSeederTable::class,
        ]);

    }
}
