<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_locations', function (Blueprint $table): void {
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', [
                'central_warehouse',
                'regional_warehouse',
                'branch',
                'storage_unit',
                'zone',
            ]);

            // Hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();

            // Temperature Specifications
            $table->decimal('temp_min', 5, 2)->nullable();
            $table->decimal('temp_max', 5, 2)->nullable();

            // Management
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('inspection_frequency_hours')->nullable();

            // Additional Information
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->boolean('has_sensor')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('parent_id');
            $table->index('responsible_user_id');
            $table->index('is_active');
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_locations');
    }
};
