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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->integer('episode_number');
            $table->integer('season_number');
            $table->string('name')->nullable();
            $table->text('overview')->nullable();
            $table->string('still_path')->nullable();
            $table->date('air_date')->nullable();
            $table->integer('runtime')->nullable();
            $table->float('vote_average')->default(0);
            $table->integer('vote_count')->default(0);
            $table->string('production_code')->nullable();
            $table->json('crew')->nullable();
            $table->json('guest_stars')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('episodes');
    }
};