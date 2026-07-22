<?php

namespace Modules\Property\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\PropertyAttribute;

class UpsertPropertyAttributeRequest extends FormRequest
{
    public const VALIDATION_CHOICES = [
        'email',
        'url',
        'integer',
        'numeric',
        'alpha',
        'alpha_num',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('Property Management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $attribute = $this->route('property_attribute');
        $isUpdate = $attribute instanceof PropertyAttribute;

        return [
            'code' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('property_attributes', 'code')->ignore($attribute?->id),
            ],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::enum(AttributeType::class)],
            'is_required' => ['nullable', 'boolean'],
            'is_unique' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'validation' => ['nullable', 'string', Rule::in(self::VALIDATION_CHOICES)],
            'regex' => ['nullable', 'string', 'max:1000'],
            'default_value' => ['nullable', 'string', 'max:5000'],
            'options' => ['nullable', 'array', 'max:200'],
            'options.*.id' => ['nullable', 'integer', 'distinct'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.is_active' => ['nullable', 'boolean'],
            'update_translations' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = AttributeType::tryFrom((string) $this->input('type'));
                $options = $this->input('options', []);

                if ($type?->hasOptions() && (! is_array($options) || count($options) === 0)) {
                    $validator->errors()->add('options', __('At least one option is required for this attribute type.'));
                }

                if ($type !== null && ! $type->hasOptions() && is_array($options) && count($options) > 0) {
                    $validator->errors()->add('options', __('Options are not allowed for this attribute type.'));
                }

                $regex = trim((string) $this->input('regex', ''));
                if ($regex !== '' && @preg_match($regex, '') === false) {
                    $validator->errors()->add('regex', __('The regular expression is invalid.'));
                }

                $textTypes = [AttributeType::Text, AttributeType::Textarea];
                $numberTypes = [AttributeType::Number, AttributeType::Price];
                $validation = $this->input('validation');
                if (in_array($validation, ['email', 'url', 'alpha', 'alpha_num'], true)
                    && ! in_array($type, $textTypes, true)
                ) {
                    $validator->errors()->add('validation', __('This validation rule is only compatible with text attributes.'));
                }
                if (in_array($validation, ['integer', 'numeric'], true)
                    && ! in_array($type, $numberTypes, true)
                ) {
                    $validator->errors()->add('validation', __('This validation rule is only compatible with numeric attributes.'));
                }
                if ($regex !== '' && ! in_array($type, $textTypes, true)) {
                    $validator->errors()->add('regex', __('Regular expressions are only compatible with text attributes.'));
                }

                if ($type?->isMedia() && $this->boolean('is_unique')) {
                    $validator->errors()->add('is_unique', __('Media attributes cannot be unique.'));
                }

                $attribute = $this->route('property_attribute');
                if ($attribute instanceof PropertyAttribute
                    && ! $attribute->is_unique
                    && $this->boolean('is_unique')
                    && $attribute->values()->exists()
                ) {
                    $validator->errors()->add('is_unique', __('Unique cannot be enabled after values have been saved.'));
                }

                $this->validateDefault($validator, $type);
            },
        ];
    }

    private function validateDefault(Validator $validator, ?AttributeType $type): void
    {
        $default = $this->input('default_value');
        if ($default === null || $default === '') {
            return;
        }

        if ($type === null || $type->hasOptions() || $type->isMedia()) {
            $validator->errors()->add('default_value', __('Defaults are not supported for option or media attributes.'));

            return;
        }

        $valid = match ($type) {
            AttributeType::Text, AttributeType::Textarea => $this->textDefaultIsValid((string) $default),
            AttributeType::Number, AttributeType::Price => $this->numberDefaultIsValid((string) $default),
            AttributeType::Boolean => in_array($default, [0, 1, '0', '1', 'true', 'false'], true),
            AttributeType::Date => $this->dateMatches((string) $default, 'Y-m-d'),
            AttributeType::Datetime => collect(['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s'])
                ->contains(fn (string $format): bool => $this->dateMatches((string) $default, $format)),
            default => false,
        };

        if (! $valid) {
            $validator->errors()->add('default_value', __('The default value is invalid for this attribute type.'));
        }
    }

    private function dateMatches(string $value, string $format): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!'.$format, $value);

        return $date !== false && $date->format($format) === $value;
    }

    private function textDefaultIsValid(string $value): bool
    {
        $validation = $this->input('validation');
        if (is_string($validation)
            && validator(['value' => $value], ['value' => [$validation]])->fails()
        ) {
            return false;
        }

        $regex = $this->input('regex');

        return ! is_string($regex) || $regex === '' || @preg_match($regex, $value) === 1;
    }

    private function numberDefaultIsValid(string $value): bool
    {
        if (preg_match('/^[+-]?\d{1,14}(?:\.\d{1,6})?$/', $value) !== 1) {
            return false;
        }

        return $this->input('validation') !== 'integer'
            || preg_match('/^[+-]?\d{1,14}$/', $value) === 1;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower(trim((string) $this->input('code'))),
            'is_required' => $this->boolean('is_required'),
            'is_unique' => $this->boolean('is_unique'),
            'is_active' => $this->boolean('is_active'),
            'update_translations' => $this->boolean('update_translations'),
            'validation' => $this->filled('validation') ? $this->input('validation') : null,
            'regex' => $this->filled('regex') ? trim((string) $this->input('regex')) : null,
            'default_value' => $this->filled('default_value') ? $this->input('default_value') : null,
        ]);
    }
}
