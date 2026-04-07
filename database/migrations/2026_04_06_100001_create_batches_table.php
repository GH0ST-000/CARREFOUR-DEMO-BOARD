<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table): void {
            $table->id();

            // Batch Identification
            $table->string('batch_number')->unique();
            $table->string('import_declaration_number')->nullable();
            $table->string('local_production_number')->nullable();

            // Dates
            $table->date('production_date');
            $table->date('expiry_date');
            $table->dateTime('receiving_datetime')->nullable();

            // Quantity & Status
            $table->decimal('quantity', 10, 2);
            $table->decimal('remaining_quantity', 10, 2);
            $table->string('unit');
            $table->enum('status', [
                'pending',
                'received',
                'in_storage',
                'in_transit',
                'blocked',
                'recalled',
                'expired',
                'disposed',
            ])->default('pending');

            // Storage Information
            $table->foreignId('warehouse_location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
            $table->decimal('receiving_temperature', 5, 2)->nullable();
            $table->text('packaging_condition')->nullable();

            // Relationships
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Additional Information
            $table->json('linked_documents')->nullable();
            $table->json('temperature_history')->nullable();
            $table->json('movement_history')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('batch_number');
            $table->index('status');
            $table->index('expiry_date');
            $table->index('product_id');
            $table->index('warehouse_location_id');
            $table->index(['product_id', 'status']);
            $table->index(['expiry_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
