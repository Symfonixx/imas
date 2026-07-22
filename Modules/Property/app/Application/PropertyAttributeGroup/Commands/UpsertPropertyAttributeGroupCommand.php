<?php

namespace Modules\Property\Application\PropertyAttributeGroup\Commands;

class UpsertPropertyAttributeGroupCommand
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public readonly array $attributes,
        public readonly bool $updateTranslations = false,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated, bool $updateTranslations = false): self
    {
        unset($validated['update_translations']);

        return new self($validated, $updateTranslations);
    }
}
