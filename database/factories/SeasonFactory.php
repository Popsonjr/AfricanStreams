<?php

namespace Database\Factories;

use App\Models\TvShow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Season>
 */
class SeasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tv_show_id' => TvShow::factory(),
            'season_number' => $this->faker->numberBetween(1, 10),
            'name' => $this->faker->sentence(3),
            'overview' => $this->faker->paragraph,
            'poster_path' => $this->faker->imageUrl(),
            'air_date' => $this->faker->date(),
            'episode_count' => $this->faker->numberBetween(6, 20),
            'vote_average' => $this->faker->randomFloat(1, 0, 10),
            '_id' => $this->faker->uuid,
        ];
    }
}