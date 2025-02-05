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
        Schema::table('movies', function (Blueprint $table) {
            // $table->dropForeign(['genre_id']);
           $table->dropColumn(['description', 'duration', 'cast', 'genre_id', 'trailer_uri', 'cover_image', 'standard_image', 'thumbnail_image', 'movie_file', 'banner_image', 'type']);

           $table->text('overview')->nullable();
           $table->string('poster_path')->nullable();
           $table->string('backdrop_path')->nullable();
           $table->float('vote_average')->default(0);
           $table->integer('vote_count')->default(0);
           $table->boolean('adult')->default(false);
           $table->string('original_language')->nullable();
           $table->string('original_title')->nullable();
           $table->integer('runtime')->nullable();
           $table->string('status')->nullable();
           $table->json('belongs_to_collection')->nullable();
           $table->json('spoken_languages')->nullable();
           $table->float('popularity')->default(0);
           $table->boolean('video')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {

            $table->dropColumn(['overview', 'poster_path', 'backdrop_path', 'vote_average', 'vote_count', 'adult', 'original_language', 'original_title', 'runtime', 'status', 'belongs_to_collection', 'spoken_languages', 'popularity', 'video']);

            
            $table->text('description')->nullable();
            $table->string('duration')->nullable();
            $table->string('cast')->nullable();
            $table->foreignId('genre_id')->nullable()->constrained()->onDelete('set null');
            $table->string('trailer_uri')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('standard_image')->nullable();
            $table->string('thumbnail_image')->nullable();
            $table->string('movie_file')->nullable();
            $table->string('banner_image')->nullable();
            $table->enum('type', ['movie', 'series']);

            // $table->foreignId('genre_id')
            // ->nullable()
            // ->constrained()
            // ->onDelete('set null');

            
        });
    }
};