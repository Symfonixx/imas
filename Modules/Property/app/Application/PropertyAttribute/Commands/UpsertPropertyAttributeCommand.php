<?php

namespace Modules\Property\Application\PropertyAttribute\Commands;

class UpsertPropertyAttributeCommand
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array{id?: int, label: string, icon?: ?string, is_active?: bool}>  $options
     */
    public function __construct(
        public readonly array $attributes,
        public readonly array $options,
        public readonly bool $updateTranslations = false,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(
        array $validated,
        bool $includeCode,
        bool $updateTranslations = false
    ): self {
        $options = array_values($validated['options'] ?? []);
        unset($validated['options'], $validated['update_translations']);

        if (! $includeCode) {
            unset($validated['code']);
        }

        return new self($validated, $options, $updateTranslations);
    }
}
