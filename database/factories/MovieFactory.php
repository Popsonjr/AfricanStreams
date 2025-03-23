<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'overview' => $this->faker->paragraph,
            'poster_path' => '/storage/movies/covers/o1AkW5ECBbBYV1WHn74DzbWCZ1kJv162kv0DBIkS.jpg',
            'backdrop_path' => '/storage/movies/standard/GHkFv0jVbqj04lPFNynj0klVQDqtl9u9YtEn6F0V.jpg',
            'release_date' => $this->faker->date(),
            'vote_average' => $this->faker->randomFloat(1, 0, 10),
            'vote_count' => $this->faker->numberBetween(0, 1000),
            'adult' => $this->faker->boolean,
            'original_language' => $this->faker->languageCode,
            'original_title' => $this->faker->sentence(3),
            'runtime' => $this->faker->numberBetween(60, 240),
            'status' => $this->faker->randomElement(['Released', 'Post Production']),
            'production_companies' => json_encode([[
                'id' => $this->faker->randomNumber(),
                'name' => $this->faker->company,
                'logo_path' => $this->faker->imageUrl(),
                'origin_country' => $this->faker->countryCode,
            ]]),
            'production_countries' => json_encode([[
                'iso_3166_1' => $this->faker->countryCode,
                'name' => $this->faker->country,
            ]]),
            'tagline' => $this->faker->sentence,
            'budget' => $this->faker->numberBetween(1000000, 200000000),
            'revenue' => $this->faker->numberBetween(1000000, 1000000000),
            'homepage' => $this->faker->url,
            'belongs_to_collection' => $this->faker->boolean ? json_encode([
                'id' => $this->faker->randomNumber(),
                'name' => $this->faker->sentence(3),
                'poster_path' => $this->faker->imageUrl(),
                'backdrop_path' => $this->faker->imageUrl(),
            ]) : null,
            'spoken_languages' => json_encode([[
                'iso_639_1' => $this->faker->languageCode,
                'name' => $this->faker->word,
            ]]),
            'imdb_id' => 'tt' . $this->faker->numberBetween(1000000, 9999999),
            'popularity' => $this->faker->randomFloat(2, 0, 1000),
            'video' => $this->faker->boolean,
            'file_path' => '/storage/movies/videos/kiC40IxCEfMff4R4iaR9IpW2JcXBJBbPdBDRZdUC.mkv',
            'trailer_url' => 'https://www.youtube.com/embed/2xb9Ty-1frw?autoplay=1&mute=1&controls=0&loop=1&playlist=2xb9Ty-1frw'
        ];
    }
}