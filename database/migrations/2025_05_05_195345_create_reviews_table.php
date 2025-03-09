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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->json('author_details')->nullable();
            $table->text('content');
            $table->string('iso_639_1')->nullable();
            $table->morphs('reviewable');
            $table->string('url')->nullable();
            $table->integer('rating'); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};