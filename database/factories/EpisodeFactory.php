<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'episode_number' => $this->faker->numberBetween(1, 20),
            'season_number' => $this->faker->numberBetween(1, 10),
            'name' => $this->faker->sentence(3),
            'overview' => $this->faker->paragraph,
            'still_path' => $this->faker->imageUrl(),
            'air_date' => $this->faker->date(),
            'runtime' => $this->faker->numberBetween(20, 60),
            'vote_average' => $this->faker->randomFloat(1, 0, 10),
            'vote_count' => $this->faker->numberBetween(0, 1000),
            'production_code' => $this->faker->bothify('???###'),
            'crew' => json_encode([['id' => $this->faker->randomNumber(), 'name' => $this->faker->name, 'job' => 'Director']]),
            'guest_stars' => json_encode([['id' => $this->faker->randomNumber(), 'name' => $this->faker->name, 'character' => $this->faker->word]]),
        ];
    }
}