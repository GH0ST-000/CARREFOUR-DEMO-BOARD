<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * @mixin Document
 */
final class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $disk = (string) ($this->disk ?: config('filesystems.default', 's3'));
        $url = null;

        try {
            // For public disks this is a direct URL; for private S3 it depends on adapter visibility.
            $filesystem = Storage::disk($disk);

            if (method_exists($filesystem, 'temporaryUrl')) {
                $url = $filesystem->temporaryUrl($this->path, Carbon::now()->addMinutes(30));
            } else {
                /** @var mixed $filesystem */
                $url = $filesystem->url($this->path);
            }
        } catch (Throwable) {
            $url = null;
        }

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'batch_id' => $this->batch_id,
            'type' => $this->type,
            'description' => $this->description,
            'source' => $this->source,
            'external_reference' => $this->external_reference,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'disk' => $this->disk,
            'path' => $this->path,
            'url' => $url,
            'uploaded_by_user_id' => $this->uploaded_by_user_id,
            'uploaded_at' => $this->uploaded_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
