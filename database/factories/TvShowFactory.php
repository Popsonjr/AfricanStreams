<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TvShow>
 */
class TvShowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'overview' => $this->faker->paragraph,
            'poster_path' => $this->faker->imageUrl(),
            'backdrop_path' => $this->faker->imageUrl(),
            'first_air_date' => $this->faker->date(),
            'last_air_date' => $this->faker->date(),
            'vote_average' => $this->faker->randomFloat(1, 0, 10),
            'vote_count' => $this->faker->numberBetween(0, 1000),
            'adult' => $this->faker->boolean,
            'original_language' => $this->faker->languageCode,
            'original_name' => $this->faker->sentence(3),
            'number_of_seasons' => $this->faker->numberBetween(1, 10),
            'number_of_episodes' => $this->faker->numberBetween(10, 100),
            'status' => $this->faker->randomElement(['Returning Series', 'Ended']),
            'type' => $this->faker->randomElement(['Scripted', 'Reality']),
            'tagline' => $this->faker->sentence,
            'homepage' => $this->faker->url,
            'in_production' => $this->faker->boolean,
            'created_by' => json_encode([['id' => $this->faker->randomNumber(), 'name' => $this->faker->name]]),
            'episode_run_time' => json_encode([$this->faker->numberBetween(20, 60)]),
            'languages' => json_encode([$this->faker->languageCode]),
            'networks' => json_encode([['id' => $this->faker->randomNumber(), 'name' => $this->faker->company]]),
            'origin_country' => json_encode([$this->faker->countryCode]),
            'production_companies' => json_encode([['id' => $this->faker->randomNumber(), 'name' => $this->faker->company]]),
            'production_countries' => json_encode([['iso_3166_1' => $this->faker->countryCode, 'name' => $this->faker->country]]),
            'spoken_languages' => json_encode([['iso_639_1' => $this->faker->languageCode, 'name' => $this->faker->word]]),
            'last_episode_to_air' => json_encode(['id' => $this->faker->randomNumber(), 'name' => $this->faker->sentence, 'air_date' => $this->faker->date()]),
            'next_episode_to_air' => $this->faker->boolean ? json_encode(['id' => $this->faker->randomNumber(), 'name' => $this->faker->sentence, 'air_date' => $this->faker->date()]) : null,
            'popularity' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}