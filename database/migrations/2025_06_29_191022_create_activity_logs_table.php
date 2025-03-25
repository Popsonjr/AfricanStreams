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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('activity'); // Human readable activity description
            $table->json('metadata')->nullable(); // Extra details like movie_id, rating, etc.
            $table->date('activity_date'); // Separate date column
            $table->time('activity_time'); // Separate time column
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'activity_date']);
            $table->index('activity_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
