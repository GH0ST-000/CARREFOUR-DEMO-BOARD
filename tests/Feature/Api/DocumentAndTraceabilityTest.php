<?php

declare(strict_types=1);

use App\Models\Batch;
use App\Models\Branch;
use App\Models\BranchProductBatch;
use App\Models\Document;
use App\Models\Product;
use App\Models\ProductTraceabilityEvent;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
        'view_product',
        'update_product',
        'update_batch',
    ]);

    config()->set('filesystems.default', 's3');
    Storage::fake('s3');
});

test('can upload a product document to s3', function (): void {
    $product = Product::factory()->create();

    $file1 = UploadedFile::fake()->create('certificate-1.pdf', 100, 'application/pdf');
    $file2 = UploadedFile::fake()->create('certificate-2.pdf', 120, 'application/pdf');

    $response = $this->actingAs($this->user, 'api')
        ->postJson(sprintf('/api/products/%d/documents', $product->id), [
            'type' => '  certificate  ',
            'description' => '  QA certificate  ',
            'external_reference' => '  EXT-1  ',
            'files' => [$file1, $file2],
        ]);

    $response->assertCreated()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.product_id', $product->id)
        ->assertJsonPath('data.0.type', 'certificate')
        ->assertJsonPath('data.0.original_name', 'certificate-1.pdf');

    $this->assertDatabaseHas('documents', [
        'product_id' => $product->id,
        'type' => 'certificate',
    ]);

    $document = Document::query()->where('product_id', $product->id)->firstOrFail();
    expect($document->product->id)->toBe($product->id);
    expect($document->batch)->toBeNull();
    expect($document->uploadedBy?->id)->toBe($this->user->id);

    // Exercise Product relations
    expect($product->documents()->count())->toBe(2);
    expect($product->batches()->count())->toBe(0);
});

test('can upload a batch document to s3', function (): void {
    $batch = Batch::factory()->create();

    $file = UploadedFile::fake()->image('safety.png');

    $response = $this->actingAs($this->user, 'api')
        ->postJson(sprintf('/api/batches/%d/documents', $batch->id), [
            'type' => 'safety_document',
            'description' => 'Safety sheet',
            'files' => [$file],
        ]);

    $response->assertCreated()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.batch_id', $batch->id)
        ->assertJsonPath('data.0.product_id', $batch->product_id)
        ->assertJsonPath('data.0.type', 'safety_document');

    $this->assertDatabaseHas('documents', [
        'batch_id' => $batch->id,
        'product_id' => $batch->product_id,
        'type' => 'safety_document',
    ]);

    $document = Document::query()->where('batch_id', $batch->id)->firstOrFail();
    expect($document->batch->id)->toBe($batch->id);
    expect($document->product->id)->toBe($batch->product_id);
    expect($batch->documents()->count())->toBe(1);
});

test('can assign product batch to a branch and retrieve traceability history', function (): void {
    $product = Product::factory()->create([
        'shelf_life_days' => 30,
    ]);

    $batch = Batch::factory()->create([
        'product_id' => $product->id,
        'batch_number' => 'LOT-001',
        'production_date' => '2026-01-01',
        'expiry_date' => '2026-01-31',
    ]);

    $toBranch = Branch::query()->create([
        'name' => 'Branch A',
        'code' => 'BR-A',
        'location' => 'Tbilisi',
        'is_active' => true,
    ]);

    $fromBranch = Branch::query()->create([
        'name' => 'Branch B',
        'code' => 'BR-B',
        'location' => 'Batumi',
        'is_active' => true,
    ]);

    $assignResponse = $this->actingAs($this->user, 'api')
        ->postJson(sprintf('/api/products/%d/branch-assignments', $product->id), [
            'to_branch_id' => $toBranch->id,
            'from_branch_id' => $fromBranch->id,
            'batch_id' => $batch->id,
            'quantity' => 5,
            'transferred_at' => '2026-02-01T10:00:00+00:00',
            'responsible_user_id' => $this->user->id,
        ]);

    $assignResponse->assertCreated()
        ->assertJsonPath('data.product_id', $product->id)
        ->assertJsonPath('data.to_branch_id', $toBranch->id)
        ->assertJsonPath('data.quantity', 5);

    $this->assertDatabaseHas('product_traceability_events', [
        'product_id' => $product->id,
        'to_branch_id' => $toBranch->id,
        'quantity' => 5.0,
    ]);

    $this->assertDatabaseHas('branch_product_batches', [
        'branch_id' => $toBranch->id,
        'product_id' => $product->id,
        'batch_number' => 'LOT-001',
    ]);

    $event = ProductTraceabilityEvent::query()->where('product_id', $product->id)->firstOrFail();
    expect($event->product->id)->toBe($product->id);
    expect($event->batch?->id)->toBe($batch->id);
    expect($event->fromBranch?->id)->toBe($fromBranch->id);
    expect($event->toBranch->id)->toBe($toBranch->id);
    expect($event->responsibleUser?->id)->toBe($this->user->id);
    expect($event->createdBy?->id)->toBe($this->user->id);

    $branchBatch = BranchProductBatch::query()
        ->where('branch_id', $toBranch->id)
        ->where('product_id', $product->id)
        ->where('batch_number', 'LOT-001')
        ->firstOrFail();
    expect($branchBatch->branch->id)->toBe($toBranch->id);
    expect($branchBatch->product->id)->toBe($product->id);
    expect($branchBatch->batch?->id)->toBe($batch->id);
    expect($branchBatch->responsibleUser?->id)->toBe($this->user->id);

    $historyResponse = $this->actingAs($this->user, 'api')
        ->getJson(sprintf('/api/products/%d/traceability?per_page=10', $product->id));

    $historyResponse->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'to_branch_id', 'quantity', 'transferred_at'],
            ],
        ]);
});

test('product expiration date is calculated from production date and shelf life', function (): void {
    $product = Product::factory()->create([
        'production_date' => '2026-01-01',
        'shelf_life_days' => 10,
    ]);

    $product->refresh();

    expect($product->expiration_date?->toDateString())->toBe('2026-01-11');

    $product->update(['production_date' => null]);
    $product->refresh();

    expect($product->expiration_date)->toBeNull();
});

test('branch can have responsible users and relations resolve', function (): void {
    $branch = Branch::query()->create([
        'name' => 'Branch Responsible',
        'code' => 'BR-R',
        'location' => null,
        'is_active' => true,
    ]);

    $branch->responsibleUsers()->attach($this->user->id, ['assigned_at' => now()]);

    expect($branch->responsibleUsers()->count())->toBe(1);
    expect($this->user->responsibleBranches()->count())->toBe(1);
});

test('traceability assignment rejects batch that does not belong to product', function (): void {
    $product = Product::factory()->create();
    $otherProduct = Product::factory()->create();
    $batch = Batch::factory()->create(['product_id' => $otherProduct->id]);

    $branch = Branch::query()->create([
        'name' => 'Branch X',
        'code' => 'BR-X',
        'location' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson(sprintf('/api/products/%d/branch-assignments', $product->id), [
            'to_branch_id' => $branch->id,
            'batch_id' => $batch->id,
            'quantity' => 1,
        ]);

    $response->assertUnprocessable();
});

test('traceability assignment requires batch_number when no batch_id is provided', function (): void {
    $product = Product::factory()->create();

    $branch = Branch::query()->create([
        'name' => 'Branch Y',
        'code' => 'BR-Y',
        'location' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user, 'api')
        ->postJson(sprintf('/api/products/%d/branch-assignments', $product->id), [
            'to_branch_id' => $branch->id,
            'quantity' => 1,
        ]);

    $response->assertUnprocessable();
});
