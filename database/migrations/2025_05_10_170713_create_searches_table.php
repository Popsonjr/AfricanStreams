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
        // Searches (for indexing)
        Schema::create('searches', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->morphs('searchable'); // Polymorphic: movie_id, tv_show_id, person_id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('searches');
    }
};