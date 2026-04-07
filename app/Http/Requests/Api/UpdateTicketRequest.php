<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateTicketRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:10000'],
            'status' => ['sometimes', 'required', 'string', 'in:open,in_progress,resolved,closed'],
            'priority' => ['sometimes', 'required', 'string', 'in:low,medium,high'],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = mb_trim((string) $this->input('title', ''));
        }

        if ($this->has('description')) {
            $data['description'] = mb_trim((string) $this->input('description', ''));
        }

        $this->merge($data);
    }
}
