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
        Schema::create('genre_tv', function (Blueprint $table) {
            $table->foreignId('tv_show_id')->constrained()->onDelete('cascade');
            $table->foreignId('genre_id')->constrained()->onDelete('cascade');
            $table->primary(['tv_show_id', 'genre_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('genre_tv');
    }
};