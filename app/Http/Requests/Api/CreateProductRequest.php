<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode'],
            'qr_code' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'origin_type' => ['required', 'string', 'in:local,imported'],
            'country_of_origin' => ['required', 'string', 'max:255'],
            'storage_temp_min' => ['nullable', 'numeric', 'min:-50', 'max:50'],
            'storage_temp_max' => ['nullable', 'numeric', 'min:-50', 'max:50', 'gte:storage_temp_min'],
            'shelf_life_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'inventory_policy' => ['required', 'string', 'in:fifo,fefo'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:100'],
            'risk_indicators' => ['nullable', 'array'],
            'risk_indicators.*' => ['string', 'max:100'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['string', 'max:100'],
            'manufacturer_id' => ['required', 'integer', 'exists:manufacturers,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->input('name', ''));
        }

        if ($this->has('sku')) {
            $data['sku'] = trim((string) $this->input('sku', ''));
        }

        if ($this->has('barcode')) {
            $data['barcode'] = $this->input('barcode') ? trim((string) $this->input('barcode')) : null;
        }

        if ($this->has('qr_code')) {
            $data['qr_code'] = $this->input('qr_code') ? trim((string) $this->input('qr_code')) : null;
        }

        if ($this->has('brand')) {
            $data['brand'] = $this->input('brand') ? trim((string) $this->input('brand')) : null;
        }

        if ($this->has('category')) {
            $data['category'] = trim((string) $this->input('category', ''));
        }

        if ($this->has('unit')) {
            $data['unit'] = trim((string) $this->input('unit', ''));
        }

        if ($this->has('country_of_origin')) {
            $data['country_of_origin'] = trim((string) $this->input('country_of_origin', ''));
        }

        $this->merge($data);
    }
}
