<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateBatchRequest extends FormRequest
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
        return [
            'batch_number' => ['required', 'string', 'max:255', 'unique:batches,batch_number'],
            'import_declaration_number' => ['nullable', 'string', 'max:255'],
            'local_production_number' => ['nullable', 'string', 'max:255'],
            'production_date' => ['required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['required', 'date', 'after:production_date'],
            'receiving_datetime' => ['nullable', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:pending,received,in_storage,in_transit,blocked,recalled,expired,disposed'],
            'warehouse_location_id' => ['nullable', 'integer', 'exists:warehouse_locations,id'],
            'receiving_temperature' => ['nullable', 'numeric', 'min:-50', 'max:50'],
            'packaging_condition' => ['nullable', 'string', 'max:1000'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'received_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'linked_documents' => ['nullable', 'array'],
            'linked_documents.*' => ['string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
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
