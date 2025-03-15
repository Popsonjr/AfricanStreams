<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('popularity');
        });
    }

    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};