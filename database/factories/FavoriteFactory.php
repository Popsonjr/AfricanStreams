<?php

namespace Database\Factories;

use App\Models\Favorite;
use App\Models\Movie;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition()
    {
        $favoritable = $this->faker->randomElement([
            Movie::inRandomOrder()->first() ?? Movie::factory(),
            TvShow::inRandomOrder()->first() ?? TvShow::factory(),
        ]);

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'favoritable_id' => $favoritable->id,
            'favoritable_type' => get_class($favoritable),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}