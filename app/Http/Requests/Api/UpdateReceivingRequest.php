<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateReceivingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batch_id' => ['sometimes', 'integer', 'exists:batches,id'],
            'warehouse_location_id' => ['sometimes', 'integer', 'exists:warehouse_locations,id'],
            'received_by_user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'verified_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'receipt_datetime' => ['sometimes', 'date'],
            'received_quantity' => ['sometimes', 'numeric', 'min:0.01'],
            'unit' => ['sometimes', 'string', 'max:50'],
            'receipt_number' => ['nullable', 'string', 'max:255'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:pending,accepted,rejected,quarantined'],
            'recorded_temperature' => ['nullable', 'numeric', 'min:-50', 'max:50'],
            'temperature_compliant' => ['nullable', 'boolean'],
            'temperature_notes' => ['nullable', 'string', 'max:1000'],
            'packaging_condition' => ['nullable', 'string', 'in:excellent,good,acceptable,damaged,rejected'],
            'quality_notes' => ['nullable', 'string', 'max:1000'],
            'documents_verified' => ['nullable', 'boolean'],
            'missing_documents' => ['nullable', 'array'],
            'missing_documents.*' => ['string', 'max:255'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string', 'max:255'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        foreach (['receipt_number', 'supplier_invoice_number', 'temperature_notes', 'quality_notes', 'rejection_reason', 'notes'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $trimmed = mb_trim($data[$field]);
                $data[$field] = $trimmed === '' ? null : $trimmed;
            }
        }

        $this->replace($data);
    }
}
