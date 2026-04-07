<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Batch
 */
final class BatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['product', 'warehouseLocation', 'receivedBy']);

        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'import_declaration_number' => $this->import_declaration_number,
            'local_production_number' => $this->local_production_number,
            'production_date' => $this->production_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'receiving_datetime' => $this->receiving_datetime?->toIso8601String(),
            'quantity' => $this->quantity,
            'remaining_quantity' => $this->remaining_quantity,
            'unit' => $this->unit,
            'status' => $this->status,
            'warehouse_location_id' => $this->warehouse_location_id,
            'warehouse_location' => $this->whenLoaded('warehouseLocation', fn () => [
                'id' => $this->warehouseLocation->id,
                'name' => $this->warehouseLocation->name,
                'code' => $this->warehouseLocation->code,
                'type' => $this->warehouseLocation->type,
            ]),
            'receiving_temperature' => $this->receiving_temperature,
            'packaging_condition' => $this->packaging_condition,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'sku' => $this->product->sku,
                'category' => $this->product->category,
            ]),
            'received_by_user_id' => $this->received_by_user_id,
            'received_by' => $this->whenLoaded('receivedBy', fn () => $this->receivedBy ? [
                'id' => $this->receivedBy->id,
                'name' => $this->receivedBy->name,
                'email' => $this->receivedBy->email,
            ] : null),
            'linked_documents' => $this->linked_documents,
            'temperature_history' => $this->temperature_history,
            'movement_history' => $this->movement_history,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
