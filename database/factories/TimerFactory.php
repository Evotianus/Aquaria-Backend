<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timer>
 */
class TimerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'minutes' => mt_rand(1, 60),
            'user_id' => mt_rand(1, 5),
            'fish_id' => mt_rand(1, 5),
            'created_at' => fake()->dateTimeBetween('-1 week', '+1 week'),
        ];
    }
}
