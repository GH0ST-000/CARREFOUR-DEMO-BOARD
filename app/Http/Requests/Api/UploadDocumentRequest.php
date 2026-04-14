<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UploadDocumentRequest extends FormRequest
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
            'type' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'in:uploaded,external'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'max:20480', 'mimetypes:application/pdf,image/jpeg,image/png,image/webp'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('type')) {
            $data['type'] = mb_trim((string) $this->input('type', ''));
        }

        if ($this->has('description')) {
            $data['description'] = $this->input('description') ? mb_trim((string) $this->input('description')) : null;
        }

        if ($this->has('external_reference')) {
            $data['external_reference'] = $this->input('external_reference') ? mb_trim((string) $this->input('external_reference')) : null;
        }

        $this->merge($data);
    }
}
