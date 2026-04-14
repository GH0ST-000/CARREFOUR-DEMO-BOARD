<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_product_batches', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();

            $table->string('batch_number');
            $table->decimal('quantity', 10, 2);

            $table->date('production_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->dateTimeTz('transferred_at')->nullable();

            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();

            $table->unique(['branch_id', 'product_id', 'batch_number']);
            $table->index(['product_id', 'batch_number']);
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_product_batches');
    }
};
