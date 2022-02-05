<?php

namespace Database\Factories;

use App\Models\Fan;
use App\Models\FanClub;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;

class FanClubFactory extends Factory
{
    protected $model = FanClub::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'fan_club_uuid'=>Str::uuid()->toString(),
            'user_id'=>9,
            'fan_id'=>Fan::factory()->create()->id,
            'local_number'=>$this->faker->phoneNumber,
            'temp_id'=>Str::random('15'),
            'temp_id_date_time'=>$this->faker->dateTime(),
            'is_active'=>1,
            'received_count' =>$this->faker->numberBetween(100,9999),
            'send_count' =>$this->faker->numberBetween(100,9999),
            'created_at'=>$this->faker->date

        ];
    }
}
