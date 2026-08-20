<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Support\EmailValidation;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'img' => 'nullable|mimes:jpeg,png,jpg,webp,avif|max:1048',
            'name' => 'required|min:3',
            'email' => EmailValidation::rules(uniqueOnUsersTable: true),
            'mobile' => ['required', 'string', 'regex:/^[0-9]{8,15}$/', 'unique:users,mobile'],
            'password' => 'required|min:6',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return EmailValidation::messages();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
