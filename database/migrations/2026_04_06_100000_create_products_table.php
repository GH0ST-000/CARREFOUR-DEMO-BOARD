<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('qr_code')->nullable();
            $table->string('brand')->nullable();

            // Classification
            $table->string('category');
            $table->string('unit');
            $table->enum('origin_type', ['local', 'imported']);
            $table->string('country_of_origin');

            // Storage & Shelf Life
            $table->decimal('storage_temp_min', 5, 2)->nullable();
            $table->decimal('storage_temp_max', 5, 2)->nullable();
            $table->integer('shelf_life_days');
            $table->enum('inventory_policy', ['fifo', 'fefo'])->default('fefo');

            // Safety & Compliance
            $table->json('allergens')->nullable();
            $table->json('risk_indicators')->nullable();
            $table->json('required_documents')->nullable();

            // Relationships
            $table->foreignId('manufacturer_id')->constrained('manufacturers')->cascadeOnDelete();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('category');
            $table->index('origin_type');
            $table->index('country_of_origin');
            $table->index('manufacturer_id');
            $table->index('is_active');
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
