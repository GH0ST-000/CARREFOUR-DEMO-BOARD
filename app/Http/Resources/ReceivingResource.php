<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Receiving;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Receiving
 */
final class ReceivingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['batch.product', 'warehouseLocation', 'receivedBy', 'verifiedBy']);

        return [
            'id' => $this->id,
            'receipt_number' => $this->receipt_number,
            'receipt_datetime' => $this->receipt_datetime->toISOString(),
            'supplier_invoice_number' => $this->supplier_invoice_number,
            'batch_id' => $this->batch_id,
            'batch' => $this->whenLoaded('batch', fn () => [
                'id' => $this->batch->id,
                'batch_number' => $this->batch->batch_number,
                'product' => $this->batch->relationLoaded('product') ? [
                    'id' => $this->batch->product->id,
                    'name' => $this->batch->product->name,
                    'sku' => $this->batch->product->sku,
                ] : null,
            ]),
            'warehouse_location_id' => $this->warehouse_location_id,
            'warehouse_location' => $this->whenLoaded('warehouseLocation', fn () => [
                'id' => $this->warehouseLocation->id,
                'name' => $this->warehouseLocation->name,
                'code' => $this->warehouseLocation->code,
                'type' => $this->warehouseLocation->type,
            ]),
            'received_by_user_id' => $this->received_by_user_id,
            'received_by' => $this->whenLoaded('receivedBy', fn () => [
                'id' => $this->receivedBy->id,
                'name' => $this->receivedBy->name,
                'email' => $this->receivedBy->email,
            ]),
            'verified_by_user_id' => $this->verified_by_user_id,
            'verified_by' => $this->whenLoaded('verifiedBy', fn () => $this->verifiedBy ? [
                'id' => $this->verifiedBy->id,
                'name' => $this->verifiedBy->name,
                'email' => $this->verifiedBy->email,
            ] : null),
            'received_quantity' => $this->received_quantity,
            'unit' => $this->unit,
            'recorded_temperature' => $this->recorded_temperature,
            'temperature_compliant' => $this->temperature_compliant,
            'temperature_notes' => $this->temperature_notes,
            'packaging_condition' => $this->packaging_condition,
            'quality_notes' => $this->quality_notes,
            'documents_verified' => $this->documents_verified,
            'missing_documents' => $this->missing_documents,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'photos' => $this->photos,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
