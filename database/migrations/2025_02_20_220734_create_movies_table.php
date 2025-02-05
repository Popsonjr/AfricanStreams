<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('release_date')->nullable();
            $table->float('vote_average')->default(0);
            $table->integer('vote_count')->default(0);
            $table->boolean('adult')->default(false);
            $table->string('original_language')->nullable();
            $table->string('original_title')->nullable();
            $table->integer('runtime')->nullable();
            $table->string('status')->nullable();
            $table->json('production_companies')->nullable(); // Array of {id, name, logo_path, origin_country}
            $table->json('production_countries')->nullable();
            $table->string('tagline')->nullable();
            $table->bigInteger('budget')->nullable();
            $table->bigInteger('revenue')->nullable();
            $table->string('homepage')->nullable();
            $table->json('belongs_to_collection')->nullable();
            $table->json('spoken_languages')->nullable();
            $table->string('imdb_id')->nullable();
            $table->float('popularity')->default(0);
            $table->boolean('video')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};