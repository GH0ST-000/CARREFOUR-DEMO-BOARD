<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class AssignProductToBranchRequest extends FormRequest
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
            'to_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'from_branch_id' => ['nullable', 'integer', 'exists:branches,id', 'different:to_branch_id'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'transferred_at' => ['nullable', 'date'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
