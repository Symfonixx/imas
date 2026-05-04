<?php

namespace Modules\Corporate\Application\Team\Commands;

use Modules\Corporate\Data\TeamData;

class UpsertTeamCommand
{
    /**
     * @param  array<string, mixed>|TeamData  $payload
     */
    public function __construct(
        public readonly array|TeamData $payload,
        public readonly bool $updateTranslations = false
    ) {}

    /**
     * @param  array<string, mixed>|TeamData  $validated
     */
    public static function fromValidated(array|TeamData $validated, bool $updateTranslations = false): self
    {
        return new self($validated, $updateTranslations);
    }
}
