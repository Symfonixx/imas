<?php

namespace Modules\Property\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\User\Enums\CmsStatus;

class StoreSlideCategoryRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:slide_categories,slug'],
            'status' => ['required', Rule::enum(CmsStatus::class)],
            'position' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('slug')),
            'status' => (string) $this->input('status', CmsStatus::PUBLISHED->value),
            'position' => $this->input('position', 0),
        ]);
    }
}
