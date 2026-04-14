<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($productId)],
            'qr_code' => ['sometimes', 'nullable', 'string', 'max:255'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'string', 'max:255'],
            'unit' => ['sometimes', 'required', 'string', 'max:50'],
            'origin_type' => ['sometimes', 'required', 'string', 'in:local,imported'],
            'country_of_origin' => ['sometimes', 'required', 'string', 'max:255'],
            'storage_temp_min' => ['sometimes', 'nullable', 'numeric', 'min:-50', 'max:50'],
            'storage_temp_max' => ['sometimes', 'nullable', 'numeric', 'min:-50', 'max:50', 'gte:storage_temp_min'],
            'production_date' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'shelf_life_days' => ['sometimes', 'required', 'integer', 'min:1', 'max:3650'],
            'inventory_policy' => ['sometimes', 'required', 'string', 'in:fifo,fefo'],
            'allergens' => ['sometimes', 'nullable', 'array'],
            'allergens.*' => ['string', 'max:100'],
            'risk_indicators' => ['sometimes', 'nullable', 'array'],
            'risk_indicators.*' => ['string', 'max:100'],
            'required_documents' => ['sometimes', 'nullable', 'array'],
            'required_documents.*' => ['string', 'max:100'],
            'manufacturer_id' => ['sometimes', 'required', 'integer', 'exists:manufacturers,id'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = mb_trim((string) $this->input('name', ''));
        }

        if ($this->has('sku')) {
            $data['sku'] = mb_trim((string) $this->input('sku', ''));
        }

        if ($this->has('barcode')) {
            $data['barcode'] = $this->input('barcode') ? mb_trim((string) $this->input('barcode')) : null;
        }

        if ($this->has('qr_code')) {
            $data['qr_code'] = $this->input('qr_code') ? mb_trim((string) $this->input('qr_code')) : null;
        }

        if ($this->has('brand')) {
            $data['brand'] = $this->input('brand') ? mb_trim((string) $this->input('brand')) : null;
        }

        if ($this->has('category')) {
            $data['category'] = mb_trim((string) $this->input('category', ''));
        }

        if ($this->has('unit')) {
            $data['unit'] = mb_trim((string) $this->input('unit', ''));
        }

        if ($this->has('country_of_origin')) {
            $data['country_of_origin'] = mb_trim((string) $this->input('country_of_origin', ''));
        }

        $this->merge($data);
    }
}
