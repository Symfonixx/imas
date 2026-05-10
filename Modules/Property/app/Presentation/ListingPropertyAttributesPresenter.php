<?php

declare(strict_types=1);

namespace Modules\Property\Presentation;

use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeValue;

final class ListingPropertyAttributesPresenter
{
    /**
     * Rows for listing cards: built-in area first (when present), then dynamic attributes
     * ordered by attribute-family pivot position, then orphan attribute values.
     *
     * @return list<array{code: string, name: string, type: string, display: string}>
     */
    public function present(Property $property): array
    {
        $rows = [];

        $area = $this->builtInAreaDisplay($property);
        if ($area !== null && $area !== '') {
            $rows[] = [
                'code' => 'built_in_area',
                'name' => __('Area'),
                'type' => 'numeric',
                'display' => $area,
            ];
        }

        $valuesByAttrId = $property->attributeValues->keyBy('attribute_id');

        $family = $property->propertyType?->attributeFamily;
        if ($family !== null) {
            foreach ($family->attributes as $attribute) {
                $value = $valuesByAttrId->get($attribute->id);
                if (! $value instanceof PropertyAttributeValue) {
                    continue;
                }

                $display = $this->formatAttributeValue($attribute, $value);
                if ($display === null || $display === '') {
                    continue;
                }

                $rows[] = $this->rowPayload($attribute, $display);
                $valuesByAttrId->forget($attribute->id);
            }
        }

        foreach ($valuesByAttrId->sortBy(fn (PropertyAttributeValue $v) => $v->attribute?->code ?? '') as $orphan) {
            $attribute = $orphan->attribute;
            if (! $attribute instanceof PropertyAttribute) {
                continue;
            }

            $display = $this->formatAttributeValue($attribute, $orphan);
            if ($display === null || $display === '') {
                continue;
            }

            $rows[] = $this->rowPayload($attribute, $display);
        }

        return array_values($rows);
    }

    /**
     * @return array{code: string, name: string, type: string, display: string}
     */
    private function rowPayload(PropertyAttribute $attribute, string $display): array
    {
        $type = $attribute->type instanceof AttributeType ? $attribute->type : AttributeType::tryFrom((string) $attribute->type);

        return [
            'code' => $attribute->code,
            'name' => (string) $attribute->name,
            'type' => $type?->value ?? 'text',
            'display' => $display,
        ];
    }

    private function builtInAreaDisplay(Property $property): ?string
    {
        $min = $property->min_area;
        $max = $property->max_area;

        if ($min === null && $max === null) {
            return null;
        }

        $minS = $this->formatAreaNumber($min);
        $maxS = $this->formatAreaNumber($max);

        if ($minS !== null && $maxS !== null && $minS !== $maxS) {
            return "{$minS} – {$maxS} ".__('sq ft');
        }

        $single = $minS ?? $maxS;

        return $single !== null ? "{$single} ".__('sq ft') : null;
    }

    private function formatAreaNumber(null|float|int|string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $n = (float) $value;
        $formatter = \NumberFormatter::create(app()->getLocale(), \NumberFormatter::DECIMAL);
        if ($formatter === false) {
            return (string) (int) round($n);
        }
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);

        return $formatter->format($n);
    }

    private function formatAttributeValue(PropertyAttribute $attribute, PropertyAttributeValue $value): ?string
    {
        $type = $attribute->type instanceof AttributeType ? $attribute->type : AttributeType::tryFrom((string) $attribute->type);

        if ($type === null) {
            return $this->nonEmptyString($value->value_text)
                ?? $this->nonEmptyString($value->value_number !== null ? (string) $value->value_number : null);
        }

        return match ($type) {
            AttributeType::Numeric => $this->numericDisplay($attribute, $value->value_number),
            AttributeType::Price => $this->priceDisplay($attribute, $value->value_number),
            AttributeType::Boolean => $value->value_boolean === null
                ? null
                : trim($attribute->name.': '.($value->value_boolean ? __('Yes') : __('No'))),
            AttributeType::Multiselect => $this->multiselectDisplay($value->value_text),
            AttributeType::Select,
            AttributeType::Text,
            AttributeType::Textarea,
            AttributeType::Color => $this->nonEmptyString($value->value_text),
            AttributeType::Date,
            AttributeType::DateTime => $this->nonEmptyString($value->value_text),
            AttributeType::Image,
            AttributeType::File => $this->fileDisplay($value->value_text),
        };
    }

    private function numericDisplay(PropertyAttribute $attribute, null|float|int|string $number): ?string
    {
        if ($number === null || $number === '') {
            return null;
        }

        $n = (float) $number;
        $formatter = \NumberFormatter::create(app()->getLocale(), \NumberFormatter::DECIMAL);
        if ($formatter === false) {
            return trim((string) $n.' '.$attribute->name);
        }
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $numStr = $formatter->format($n);

        return trim($numStr.' '.$attribute->name);
    }

    private function priceDisplay(PropertyAttribute $attribute, null|float|int|string $number): ?string
    {
        if ($number === null || $number === '') {
            return null;
        }

        $n = (float) $number;
        $formatter = \NumberFormatter::create(app()->getLocale(), \NumberFormatter::CURRENCY);
        if ($formatter === false) {
            return trim($attribute->name.': $'.number_format($n, 2));
        }

        return trim($attribute->name.': '.$formatter->formatCurrency($n, 'USD'));
    }

    private function multiselectDisplay(?string $json): ?string
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        if (! \is_array($decoded)) {
            return $this->nonEmptyString($json);
        }

        $parts = array_filter(array_map(strval(...), $decoded), fn (string $s) => $s !== '');

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function fileDisplay(?string $path): ?string
    {
        $s = $this->nonEmptyString($path);
        if ($s === null) {
            return null;
        }

        return basename(str_replace('\\', '/', $s));
    }

    private function nonEmptyString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
