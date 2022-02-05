<?php

namespace Database\Seeders;

use App\Models\Fan;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class FanSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Fan::factory()
            ->count(2)
            ->state(new Sequence(
                ['gender' => 'Male'],
                ['gender' => 'Female'],
            ))

            ->create();
    }
}
