<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Batch;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class BatchDocumentController extends Controller
{
    public function store(UploadDocumentRequest $request, Batch $batch): JsonResponse
    {
        $this->authorize('update', $batch);

        $data = $request->validated();

        $source = (string) ($data['source'] ?? 'uploaded');

        /** @var array<int, UploadedFile> $files */
        $files = $request->file('files', []);

        $disk = config('filesystems.default', 's3');
        $documents = [];

        foreach ($files as $file) {
            $path = sprintf(
                'products/%d/batches/%d/documents/%s.%s',
                $batch->product_id,
                $batch->id,
                (string) Str::uuid(),
                $file->getClientOriginalExtension() ?: 'bin',
            );

            Storage::disk($disk)->putFileAs(
                dirname($path),
                $file,
                basename($path),
                ['visibility' => 'private'],
            );

            $documents[] = Document::query()->create([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => (string) ($file->getClientMimeType() ?? $file->getMimeType() ?? 'application/octet-stream'),
                'size_bytes' => (int) $file->getSize(),
                'source' => $source,
                'external_reference' => $data['external_reference'] ?? null,
                'uploaded_by_user_id' => auth('api')->id(),
                'uploaded_at' => now(),
            ]);
        }

        /** @var AnonymousResourceCollection $resource */
        $resource = DocumentResource::collection(collect($documents));

        return $resource->response()->setStatusCode(201);
    }
}
