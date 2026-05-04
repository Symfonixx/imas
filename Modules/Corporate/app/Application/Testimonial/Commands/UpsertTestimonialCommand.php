<?php

namespace Modules\Corporate\Application\Testimonial\Commands;

use Modules\Corporate\Data\TestimonialData;

class UpsertTestimonialCommand
{
    /**
     * @param  array<string, mixed>|TestimonialData  $payload
     */
    public function __construct(
        public readonly array|TestimonialData $payload,
        public readonly bool $updateTranslations = false
    ) {}

    /**
     * @param  array<string, mixed>|TestimonialData  $validated
     */
    public static function fromValidated(array|TestimonialData $validated, bool $updateTranslations = false): self
    {
        return new self($validated, $updateTranslations);
    }
}
