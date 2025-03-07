<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        $reviewable = $this->faker->randomElement([
            Movie::inRandomOrder()->first() ?? Movie::factory(),
            TvShow::inRandomOrder()->first() ?? TvShow::factory(),
        ]);

        return [
            'reviewable_id' => $reviewable->id,
            'reviewable_type' => get_class($reviewable),
            'content' => $this->faker->paragraphs(3, true),
            'author' => $this->faker->name,
            'rating' => $this->faker->numberBetween(1, 10),
            'created_at' => $this->faker->dateTimeThisYear,
            'updated_at' => $this->faker->dateTimeThisYear,
        ];
    }
}