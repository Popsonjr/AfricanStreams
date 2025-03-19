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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('duration_months');
            $table->text('benefits')->nullable();
            $table->unsignedBigInteger('amount'); // In kobo (e.g., N2400 = 240000 kobo)
            $table->string('interval')->nullable(); // e.g., monthly, quarterly
            $table->string('paystack_plan_code')->nullable(); // Paystack plan code
            $table->boolean('active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};