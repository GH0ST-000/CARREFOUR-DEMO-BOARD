<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivings', function (Blueprint $table): void {
            $table->id();

            // Receipt Information
            $table->string('receipt_number')->nullable()->unique();
            $table->dateTime('receipt_datetime');
            $table->string('supplier_invoice_number')->nullable();

            // Batch Reference
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('warehouse_location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();

            // Quantity & Quality
            $table->decimal('received_quantity', 10, 2);
            $table->string('unit');
            $table->decimal('recorded_temperature', 5, 2)->nullable();
            $table->boolean('temperature_compliant')->default(true);
            $table->text('temperature_notes')->nullable();

            // Quality Inspection
            $table->enum('packaging_condition', [
                'excellent',
                'good',
                'acceptable',
                'damaged',
                'rejected',
            ])->default('good');
            $table->text('quality_notes')->nullable();
            $table->boolean('documents_verified')->default(false);
            $table->json('missing_documents')->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'quarantined',
            ])->default('pending');
            $table->text('rejection_reason')->nullable();

            // Photo Evidence
            $table->json('photos')->nullable();

            // Users
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Additional Information
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('receipt_number');
            $table->index('receipt_datetime');
            $table->index('batch_id');
            $table->index('warehouse_location_id');
            $table->index('status');
            $table->index('received_by_user_id');
            $table->index(['status', 'receipt_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivings');
    }
};
