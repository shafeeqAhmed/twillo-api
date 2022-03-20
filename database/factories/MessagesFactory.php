<?php

namespace Database\Factories;

use App\Models\Messages;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessagesFactory extends Factory
{
    protected $model = Messages::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'fan_id'=>rand(1,5),
            'user_id'=>1,
        ];
    }
}
