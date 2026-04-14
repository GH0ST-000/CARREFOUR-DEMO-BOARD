<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->cascadeOnDelete();

            $table->string('type'); // e.g. certificate, safety_document, compliance
            $table->string('description')->nullable();

            $table->string('disk')->default('s3');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');

            $table->enum('source', ['uploaded', 'external'])->default('uploaded');
            $table->string('external_reference')->nullable();

            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTimeTz('uploaded_at');

            $table->timestampsTz();

            $table->index(['product_id', 'type']);
            $table->index(['batch_id', 'type']);
            $table->index('uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
