<?php

declare(strict_types=1);

namespace Src\Interface\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
