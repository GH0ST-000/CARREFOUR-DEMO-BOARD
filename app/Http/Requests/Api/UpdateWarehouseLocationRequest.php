<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseLocationRequest extends FormRequest
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
        $locationId = $this->route('warehouse_location');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('warehouse_locations', 'code')->ignore($locationId)],
            'type' => ['sometimes', 'required', 'string', 'in:central_warehouse,regional_warehouse,branch,storage_unit,zone'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:warehouse_locations,id'],
            'temp_min' => ['sometimes', 'nullable', 'numeric', 'min:-50', 'max:50'],
            'temp_max' => ['sometimes', 'nullable', 'numeric', 'min:-50', 'max:50', 'gte:temp_min'],
            'responsible_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'inspection_frequency_hours' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:168'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'has_sensor' => ['sometimes', 'nullable', 'boolean'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->input('name', ''));
        }

        if ($this->has('code')) {
            $data['code'] = trim((string) $this->input('code', ''));
        }

        if ($this->has('description')) {
            $data['description'] = $this->input('description') ?
                trim((string) $this->input('description')) : null;
        }

        if ($this->has('address')) {
            $data['address'] = $this->input('address') ?
                trim((string) $this->input('address')) : null;
        }

        $this->merge($data);
    }
}
