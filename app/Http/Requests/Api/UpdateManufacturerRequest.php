<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManufacturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:255'],
            'legal_form' => ['sometimes', 'string', 'max:255'],
            'identification_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('manufacturers', 'identification_number')->ignore($this->route('manufacturer')),
            ],
            'legal_address' => ['sometimes', 'string', 'max:500'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'email' => ['sometimes', 'email', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'region' => ['sometimes', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identification_number.unique' => 'This identification number is already registered.',
            'email.email' => 'Email address must be valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('full_name')) {
            $data['full_name'] = trim((string) $this->input('full_name', ''));
        }

        if ($this->has('short_name')) {
            $data['short_name'] = $this->input('short_name') ? trim((string) $this->input('short_name')) : null;
        }

        if ($this->has('legal_form')) {
            $data['legal_form'] = trim((string) $this->input('legal_form', ''));
        }

        if ($this->has('identification_number')) {
            $data['identification_number'] = trim((string) $this->input('identification_number', ''));
        }

        if ($this->has('legal_address')) {
            $data['legal_address'] = trim((string) $this->input('legal_address', ''));
        }

        if ($this->has('phone')) {
            $data['phone'] = trim((string) $this->input('phone', ''));
        }

        if ($this->has('email')) {
            $data['email'] = trim((string) $this->input('email', ''));
        }

        if ($this->has('country')) {
            $data['country'] = trim((string) $this->input('country', ''));
        }

        if ($this->has('region')) {
            $data['region'] = trim((string) $this->input('region', ''));
        }

        if ($this->has('city')) {
            $data['city'] = $this->input('city') ? trim((string) $this->input('city')) : null;
        }

        $this->merge($data);
    }
}
