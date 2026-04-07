<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class CreateWarehouseLocationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:warehouse_locations,code'],
            'type' => ['required', 'string', 'in:central_warehouse,regional_warehouse,branch,storage_unit,zone'],
            'parent_id' => ['nullable', 'integer', 'exists:warehouse_locations,id'],
            'temp_min' => ['nullable', 'numeric', 'min:-50', 'max:50'],
            'temp_max' => ['nullable', 'numeric', 'min:-50', 'max:50', 'gte:temp_min'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'inspection_frequency_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'has_sensor' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = mb_trim((string) $this->input('name', ''));
        }

        if ($this->has('code')) {
            $data['code'] = mb_trim((string) $this->input('code', ''));
        }

        if ($this->has('description')) {
            $data['description'] = $this->input('description') ?
                mb_trim((string) $this->input('description')) : null;
        }

        if ($this->has('address')) {
            $data['address'] = $this->input('address') ?
                mb_trim((string) $this->input('address')) : null;
        }

        $this->merge($data);
    }
}
