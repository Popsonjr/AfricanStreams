<?php

namespace Database\Factories;

use App\Models\ListItem;
use App\Models\ListModel;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListItem>
 */
class ListItemFactory extends Factory
{
    protected $model = ListItem::class;

    public function definition()
    {
        $itemable = $this->faker->randomElement([
            Movie::inRandomOrder()->first() ?? Movie::factory(),
            TvShow::inRandomOrder()->first() ?? TvShow::factory(),
        ]);

        return [
            'list_id' => ListModel::inRandomOrder()->first()->id ?? ListModel::factory(),
            'itemable_id' => $itemable->id,
            'itemable_type' => get_class($itemable),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}