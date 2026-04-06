<?php

namespace App\Http\Requests\Api;

use App\Models\Batch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>|ValidationRule|string>
     */
    public function rules(): array
    {
        $batchId = $this->route('batch');
        $batch = $batchId instanceof Batch ? $batchId : Batch::find($batchId);
        $currentQuantity = $batch?->quantity ?? null;

        $rules = [
            'batch_number' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('batches', 'batch_number')->ignore($batchId)],
            'import_declaration_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'local_production_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'production_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['sometimes', 'required', 'date', 'after:production_date'],
            'receiving_datetime' => ['sometimes', 'nullable', 'date'],
            'quantity' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'unit' => ['sometimes', 'required', 'string', 'max:50'],
            'status' => ['sometimes', 'required', 'string', 'in:pending,received,in_storage,in_transit,blocked,recalled,expired,disposed'],
            'warehouse_location_id' => ['sometimes', 'nullable', 'integer', 'exists:warehouse_locations,id'],
            'receiving_temperature' => ['sometimes', 'nullable', 'numeric', 'min:-50', 'max:50'],
            'packaging_condition' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'received_by_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'linked_documents' => ['sometimes', 'nullable', 'array'],
            'linked_documents.*' => ['string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];

        // Add remaining_quantity validation with dynamic max based on updated or existing quantity
        if ($this->has('remaining_quantity')) {
            $maxQuantity = $this->has('quantity') ? $this->input('quantity') : $currentQuantity;
            $rules['remaining_quantity'] = ['required', 'numeric', 'min:0', 'lte:'.$maxQuantity];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('batch_number')) {
            $data['batch_number'] = trim((string) $this->input('batch_number', ''));
        }

        if ($this->has('import_declaration_number')) {
            $data['import_declaration_number'] = $this->input('import_declaration_number') ?
                trim((string) $this->input('import_declaration_number')) : null;
        }

        if ($this->has('local_production_number')) {
            $data['local_production_number'] = $this->input('local_production_number') ?
                trim((string) $this->input('local_production_number')) : null;
        }

        if ($this->has('unit')) {
            $data['unit'] = trim((string) $this->input('unit', ''));
        }

        if ($this->has('packaging_condition')) {
            $data['packaging_condition'] = $this->input('packaging_condition') ?
                trim((string) $this->input('packaging_condition')) : null;
        }

        if ($this->has('notes')) {
            $data['notes'] = $this->input('notes') ? trim((string) $this->input('notes')) : null;
        }

        $this->merge($data);
    }
}
