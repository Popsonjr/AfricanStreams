<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tv_shows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('first_air_date')->nullable();
            $table->date('last_air_date')->nullable();
            $table->float('vote_average')->default(0);
            $table->integer('vote_count')->default(0);
            $table->boolean('adult')->default(false);
            $table->string('original_language')->nullable();
            $table->string('original_name')->nullable();
            $table->integer('number_of_seasons')->default(0);
            $table->integer('number_of_episodes')->default(0);
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('tagline')->nullable();
            $table->string('homepage')->nullable();
            $table->boolean('in_production')->default(false);
            $table->json('created_by')->nullable();
            $table->json('episode_run_time')->nullable();
            $table->json('languages')->nullable();
            $table->json('networks')->nullable();
            $table->json('origin_country')->nullable();
            $table->json('production_companies')->nullable();
            $table->json('production_countries')->nullable();
            $table->json('spoken_languages')->nullable();
            $table->json('last_episode_to_air')->nullable();
            $table->json('next_episode_to_air')->nullable();
            $table->float('popularity')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tv_shows');
    }
};