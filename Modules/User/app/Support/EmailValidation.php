<?php

namespace Modules\User\Support;

use Illuminate\Contracts\Validation\ValidationRule;

class EmailValidation
{
    public const PATTERN = '/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/';

    /**
     * @return array<int, string|ValidationRule>
     */
    public static function rules(bool $uniqueOnUsersTable = false): array
    {
        $rules = [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            'regex:'.self::PATTERN,
        ];

        if ($uniqueOnUsersTable) {
            $rules[] = 'unique:users,email';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'email.required' => __('Please enter an email address.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.regex' => __('Please enter a valid email address.'),
            'email.unique' => __('This email is already registered.'),
            'email.max' => __('Email may not be greater than 255 characters.'),
        ];
    }
}
