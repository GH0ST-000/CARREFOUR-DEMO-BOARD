<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturers', function (Blueprint $table): void {
            $table->id();

            // Basic Information
            $table->string('full_name');
            $table->string('short_name')->nullable();
            $table->string('legal_form');
            $table->string('identification_number')->unique();

            // Contact Information
            $table->string('legal_address');
            $table->string('phone');
            $table->string('email');

            // Geography
            $table->string('country');
            $table->string('region');
            $table->string('city')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for common queries
            $table->index('is_active');
            $table->index('country');
            $table->index(['country', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturers');
    }
};
