<?php

namespace Database\Factories;

use App\Models\CodLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CodLocation>
 */
class CodLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->word,
            'address' => $this->faker->address,
            'latitude' => $this->faker->latitude(-6.2, -3.5),
            'longitude' => $this->faker->longitude(95.3, 141),
            'is_default' => $this->faker->boolean,
        ];
    }
}
