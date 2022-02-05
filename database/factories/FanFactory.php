<?php

namespace Database\Factories;

use App\Models\Fan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FanFactory extends Factory
{
    protected $model = Fan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'fan_uuid'=>Str::uuid()->toString(),
            'fname'=>$this->faker->name,
            'lname'=>$this->faker->name,
            'email'=>$this->faker->email,
            'gender'=>"Male",
            'profile_photo_path'=>$this->faker->imageUrl,
            'phone_no'=>$this->faker->phoneNumber,
            'city'=>$this->faker->city,
            'dob'=>$this->faker->date,
            'twitter'=>$this->faker->imageUrl,
            'instagram'=>$this->faker->imageUrl,
            'ticktok'=>$this->faker->imageUrl,
            'latitude'=>$this->faker->latitude,
            'longitude'=>$this->faker->latitude,
            'created_at'=>$this->faker->dateTime(),
        ];
    }
}
