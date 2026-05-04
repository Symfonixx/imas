<?php

namespace Modules\Corporate\Application\CorporateService\Commands;

use Modules\Corporate\Data\CorporateServiceData;

class UpsertCorporateServiceCommand
{
    /**
     * @param  array<string, mixed>|CorporateServiceData  $payload
     */
    public function __construct(
        public readonly array|CorporateServiceData $payload,
        public readonly bool $updateTranslations = false
    ) {}

    /**
     * @param  array<string, mixed>|CorporateServiceData  $validated
     */
    public static function fromValidated(array|CorporateServiceData $validated, bool $updateTranslations = false): self
    {
        return new self($validated, $updateTranslations);
    }
}
