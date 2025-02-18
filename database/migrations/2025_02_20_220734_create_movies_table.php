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
            $table->text('description')->nullable();
            $table->date('release_date')->nullable();
            $table->string('duration')->nullable();
            $table->string('cast')->nullable();
            $table->foreignId('genre_id')->nullable()->constrained()->onDelete('set null');
            $table->string('trailer_uri')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('standard_image')->nullable();
            $table->string('thumbnail_image')->nullable();
            $table->enum('type', ['movie', 'series']);
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