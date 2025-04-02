<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('watch_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');
            $table->timestamp('watched_at')->useCurrent();
            $table->integer('progress')->default(0); // Seconds watched
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('watch_histories');
    }
};