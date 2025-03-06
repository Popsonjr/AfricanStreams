<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\Rating;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition()
    {
        $rateable = $this->faker->randomElement([
            Movie::inRandomOrder()->first() ?? Movie::factory(),
            TvShow::inRandomOrder()->first() ?? TvShow::factory(),
            Episode::inRandomOrder()->first() ?? Episode::factory(),
        ]);

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'rateable_id' => $rateable->id,
            'rateable_type' => get_class($rateable),
            'value' => $this->faker->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}