<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;



namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\ListItem;
use App\Models\ListModel;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Season;
use App\Models\TvShow;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Users (without sessions or guest sessions)
        User::factory(10)->create();

        // Genres
        $movieGenres = [
            ['name' => 'Action', 'type' => 'movie'],
            ['name' => 'Adventure', 'type' => 'movie'],
            ['name' => 'Comedy', 'type' => 'movie'],
            ['name' => 'Drama', 'type' => 'movie'],
            ['name' => 'Sci-Fi', 'type' => 'movie'],
        ];
        $tvGenres = [
            ['name' => 'Drama', 'type' => 'tv'],
            ['name' => 'Comedy', 'type' => 'tv'],
            ['name' => 'Reality', 'type' => 'tv'],
            ['name' => 'Documentary', 'type' => 'tv'],
            ['name' => 'Crime', 'type' => 'tv'],
        ];
        foreach (array_merge($movieGenres, $tvGenres) as $genre) {
            Genre::factory()->create($genre);
        }

        // People
        Person::factory(50)->create();

        // Movies
        Movie::factory(1000)->create()->each(function ($movie) {
            $movie->genres()->attach(Genre::where('type', 'movie')->inRandomOrder()->take(3)->pluck('id'));
            $movie->credits()->createMany(
                Person::inRandomOrder()->take(5)->get()->map(function ($person) {
                    return [
                        'credit_id' => \Illuminate\Support\Str::uuid(),
                        'person_id' => $person->id,
                        'department' => 'Acting',
                        'job' => 'Actor',
                        'character' => \Illuminate\Support\Str::random(10),
                        'order' => rand(0, 10),
                    ];
                })->toArray()
            );
            Review::factory()->create([
                'reviewable_id' => $movie->id,
                'reviewable_type' => Movie::class,
            ]);
        });

        // TV Shows
        TvShow::factory(50)->create()->each(function ($tvShow) {
            $tvShow->genres()->attach(Genre::where('type', 'tv')->inRandomOrder()->take(3)->pluck('id'));
            $tvShow->credits()->createMany(
                Person::inRandomOrder()->take(5)->get()->map(function ($person) {
                    return [
                        'credit_id' => \Illuminate\Support\Str::uuid(),
                        'person_id' => $person->id,
                        'department' => 'Acting',
                        'job' => 'Actor',
                        'character' => \Illuminate\Support\Str::random(10),
                        'order' => rand(0, 10),
                    ];
                })->toArray()
            );
            Review::factory()->create([
                'reviewable_id' => $tvShow->id,
                'reviewable_type' => TvShow::class,
            ]);

            // Seasons
            Season::factory(3)->create(['tv_show_id' => $tvShow->id])->each(function ($season) {
                // Episodes
                Episode::factory(10)->create(['season_id' => $season->id])->each(function ($episode) {
                    $episode->credits()->createMany(
                        Person::inRandomOrder()->take(3)->get()->map(function ($person) {
                            return [
                                'credit_id' => \Illuminate\Support\Str::uuid(),
                                'person_id' => $person->id,
                                'department' => 'Acting',
                                'job' => 'Actor',
                                'character' => \Illuminate\Support\Str::random(10),
                                'order' => rand(0, 10),
                            ];
                        })->toArray()
                    );
                });
            });
        });

        // User Interactions
        User::all()->each(function ($user) {
            // Favorites
            Favorite::factory()->create([
                'user_id' => $user->id,
                'favoritable_id' => Movie::inRandomOrder()->first()->id,
                'favoritable_type' => Movie::class,
            ]);
            Favorite::factory()->create([
                'user_id' => $user->id,
                'favoritable_id' => TvShow::inRandomOrder()->first()->id,
                'favoritable_type' => TvShow::class,
            ]);

            // Ratings
            Rating::factory()->create([
                'user_id' => $user->id,
                'rateable_id' => Movie::inRandomOrder()->first()->id,
                'rateable_type' => Movie::class,
                'value' => rand(1, 10),
            ]);
            Rating::factory()->create([
                'user_id' => $user->id,
                'rateable_id' => TvShow::inRandomOrder()->first()->id,
                'rateable_type' => TvShow::class,
                'value' => rand(1, 10),
            ]);
            Rating::factory()->create([
                'user_id' => $user->id,
                'rateable_id' => Episode::inRandomOrder()->first()->id,
                'rateable_type' => Episode::class,
                'value' => rand(1, 10),
            ]);

            // Watchlists
            Watchlist::factory()->create([
                'user_id' => $user->id,
                'watchable_id' => Movie::inRandomOrder()->first()->id,
                'watchable_type' => Movie::class,
            ]);
            Watchlist::factory()->create([
                'user_id' => $user->id,
                'watchable_id' => TvShow::inRandomOrder()->first()->id,
                'watchable_type' => TvShow::class,
            ]);

            // Lists
            $list = ListModel::factory()->create(['user_id' => $user->id]);
            ListItem::factory()->create([
                'list_id' => $list->id,
                'itemable_id' => Movie::inRandomOrder()->first()->id,
                'itemable_type' => Movie::class,
            ]);
            ListItem::factory()->create([
                'list_id' => $list->id,
                'itemable_id' => TvShow::inRandomOrder()->first()->id,
                'itemable_type' => TvShow::class,
            ]);
        });
    }
}