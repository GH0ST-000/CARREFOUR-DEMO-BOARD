<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_traceability_events', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();

            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();

            $table->decimal('quantity', 10, 2);
            $table->dateTimeTz('transferred_at');

            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();

            $table->index(['product_id', 'transferred_at']);
            $table->index(['to_branch_id', 'transferred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_traceability_events');
    }
};
