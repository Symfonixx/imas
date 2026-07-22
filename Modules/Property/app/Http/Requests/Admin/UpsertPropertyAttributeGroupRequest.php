<?php

namespace Modules\Property\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpsertPropertyAttributeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('Property Management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'position' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'update_translations' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'update_translations' => $this->boolean('update_translations'),
        ]);
    }
}
