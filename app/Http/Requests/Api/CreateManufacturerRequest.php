<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateManufacturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:255'],
            'legal_form' => ['required', 'string', 'max:255'],
            'identification_number' => ['required', 'string', 'max:255', 'unique:manufacturers,identification_number'],
            'legal_address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
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
            'full_name.required' => 'Full name is required.',
            'legal_form.required' => 'Legal form is required.',
            'identification_number.required' => 'Identification number is required.',
            'identification_number.unique' => 'This identification number is already registered.',
            'legal_address.required' => 'Legal address is required.',
            'phone.required' => 'Phone number is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Email address must be valid.',
            'country.required' => 'Country is required.',
            'region.required' => 'Region is required.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => trim((string) $this->input('full_name', '')),
            'short_name' => $this->input('short_name') ? trim((string) $this->input('short_name')) : null,
            'legal_form' => trim((string) $this->input('legal_form', '')),
            'identification_number' => trim((string) $this->input('identification_number', '')),
            'legal_address' => trim((string) $this->input('legal_address', '')),
            'phone' => trim((string) $this->input('phone', '')),
            'email' => trim((string) $this->input('email', '')),
            'country' => trim((string) $this->input('country', '')),
            'region' => trim((string) $this->input('region', '')),
            'city' => $this->input('city') ? trim((string) $this->input('city')) : null,
        ]);
    }
}
