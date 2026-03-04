<?php

namespace App\Http\Requests\Api;

use App\Enums\ItemStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'exists:phone_numbers,number'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.status' => ['required', 'string', Rule::in([ItemStatus::SENT->value, ItemStatus::FAILED->value])],
            'items.*.response' => ['nullable', 'array'],
        ];
    }
}
