<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\TvShow;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Watchlist>
 */
class WatchlistFactory extends Factory
{
    protected $model = Watchlist::class;

    public function definition()
    {
        $watchable = $this->faker->randomElement([
            Movie::inRandomOrder()->first() ?? Movie::factory(),
            TvShow::inRandomOrder()->first() ?? TvShow::factory(),
        ]);

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'watchable_id' => $watchable->id,
            'watchable_type' => get_class($watchable),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}