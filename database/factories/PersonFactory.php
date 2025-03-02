<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'profile_path' => $this->faker->imageUrl(200, 300),
            'known_for_department' => $this->faker->randomElement(['Acting', 'Directing', 'Writing', 'Production']),
            'popularity' => $this->faker->randomFloat(2, 0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}