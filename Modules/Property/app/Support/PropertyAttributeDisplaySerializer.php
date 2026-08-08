<?php

namespace Modules\Property\Support;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Modules\Base\Support\Media\MediaAssetResolver;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeOption;
use Modules\Property\Models\PropertyAttributeValue;

final class PropertyAttributeDisplaySerializer
{
    /**
     * Attribute types whose value needs the full row width instead of sitting
     * beside the attribute name.
     */
    private const BLOCK_TYPES = [
        AttributeType::Textarea,
        AttributeType::Image,
        AttributeType::Gallery,
        AttributeType::Checkbox,
        AttributeType::Multiselect,
    ];

    /**
     * Filled attribute values for the front-office show page, ordered by
     * attribute group position and then by the attribute position inside it.
     *
     * @return list<array{
     *     id: int,
     *     code: string,
     *     name: string,
     *     help_text: ?string,
     *     type: string,
     *     icon_url: ?string,
     *     layout: string,
     *     value: mixed
     * }>
     */
    public static function forProperty(Property $property): array
    {
        if (! $property->relationLoaded('attributeGroups') || ! $property->relationLoaded('attributeValues')) {
            return [];
        }

        /** @var Collection<int, PropertyAttributeValue> $values */
        $values = $property->attributeValues->keyBy('attribute_id');
        $attributes = self::orderedAttributes($property);
        $resolved = self::resolveImages($attributes, $values);

        $rows = [];
        foreach ($attributes as $attribute) {
            $value = self::value($attribute, $values->get($attribute->id), $resolved);
            if (self::isEmpty($value)) {
                continue;
            }

            $rows[] = [
                'id' => (int) $attribute->id,
                'code' => (string) $attribute->code,
                'name' => (string) $attribute->name,
                'help_text' => $attribute->help_text !== '' ? $attribute->help_text : null,
                'type' => $attribute->type->value,
                'icon_url' => $attribute->image_link,
                'layout' => in_array($attribute->type, self::BLOCK_TYPES, true) ? 'block' : 'inline',
                'value' => $value,
            ];
        }

        return $rows;
    }

    /**
     * @return list<PropertyAttribute>
     */
    private static function orderedAttributes(Property $property): array
    {
        $attributes = [];
        foreach ($property->attributeGroups as $group) {
            if (! $group->relationLoaded('attributes')) {
                continue;
            }

            foreach ($group->attributes as $attribute) {
                $attributes[$attribute->id] ??= $attribute;
            }
        }

        return array_values($attributes);
    }

    /**
     * Single media-library lookup for every image path referenced by the
     * property's image and gallery attributes.
     *
     * @param  list<PropertyAttribute>  $attributes
     * @param  Collection<int, PropertyAttributeValue>  $values
     * @return array<string, array<string, mixed>>
     */
    private static function resolveImages(array $attributes, Collection $values): array
    {
        $paths = [];
        foreach ($attributes as $attribute) {
            $value = $values->get($attribute->id);
            if ($value === null) {
                continue;
            }

            if ($attribute->type === AttributeType::Image && is_string($value->text_value)) {
                $paths[] = $value->text_value;

                continue;
            }

            if ($attribute->type === AttributeType::Gallery) {
                foreach ($value->json_value ?? [] as $path) {
                    if (is_string($path)) {
                        $paths[] = $path;
                    }
                }
            }
        }

        if ($paths === []) {
            return [];
        }

        return app(MediaAssetResolver::class)->resolveMany($paths);
    }

    /**
     * @param  array<string, array<string, mixed>>  $resolved
     */
    private static function value(
        PropertyAttribute $attribute,
        ?PropertyAttributeValue $value,
        array $resolved,
    ): mixed {
        if ($value === null) {
            return null;
        }

        return match ($attribute->type) {
            AttributeType::Text, AttributeType::Textarea => self::text($value->text_value),
            AttributeType::Number, AttributeType::Price => $value->decimal_value === null
                ? null
                : (float) $value->decimal_value,
            AttributeType::Boolean => $value->boolean_value,
            AttributeType::Radio, AttributeType::Select => self::option($attribute, $value->integer_value),
            AttributeType::Checkbox, AttributeType::Multiselect => self::options($attribute, $value->json_value ?? []),
            AttributeType::Image => self::image($value->text_value, $attribute, $resolved),
            AttributeType::Gallery => self::gallery($value->json_value ?? [], $attribute, $resolved),
            AttributeType::File => self::file($value->text_value),
            AttributeType::Date => $value->date_value?->format('Y-m-d'),
            AttributeType::Datetime => $value->datetime_value?->format(DateTimeInterface::ATOM),
        };
    }

    private static function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return array{id: int, label: string, icon: ?string}|null
     */
    private static function option(PropertyAttribute $attribute, ?int $optionId): ?array
    {
        if ($optionId === null) {
            return null;
        }

        return self::options($attribute, [$optionId])[0] ?? null;
    }

    /**
     * @param  array<int, mixed>  $optionIds
     * @return list<array{id: int, label: string, icon: ?string}>
     */
    private static function options(PropertyAttribute $attribute, array $optionIds): array
    {
        if ($optionIds === [] || ! $attribute->relationLoaded('options')) {
            return [];
        }

        $selected = array_map('intval', array_filter($optionIds, 'is_numeric'));

        return $attribute->options
            ->filter(static fn (PropertyAttributeOption $option): bool => in_array(
                (int) $option->id,
                $selected,
                true
            ))
            ->map(static function (PropertyAttributeOption $option): array {
                $icon = is_string($option->icon) ? trim($option->icon) : '';

                return [
                    'id' => (int) $option->id,
                    'label' => (string) $option->label,
                    'icon' => $icon !== '' ? $icon : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $resolved
     * @return array{url: string, alt: string}|null
     */
    private static function image(mixed $path, PropertyAttribute $attribute, array $resolved): ?array
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $meta = $resolved[MediaAssetResolver::normalizePath($path)] ?? null;

        return [
            'url' => is_string($meta['url'] ?? null) ? $meta['url'] : asset('storage/'.$path),
            'alt' => self::text($meta['alt_text'] ?? null) ?? (string) $attribute->name,
        ];
    }

    /**
     * @param  array<int, mixed>  $paths
     * @param  array<string, array<string, mixed>>  $resolved
     * @return list<array{url: string, alt: string}>
     */
    private static function gallery(array $paths, PropertyAttribute $attribute, array $resolved): array
    {
        $images = [];
        foreach ($paths as $path) {
            $image = self::image($path, $attribute, $resolved);
            if ($image !== null) {
                $images[] = $image;
            }
        }

        return $images;
    }

    /**
     * @return array{url: string, name: string, extension: string}|null
     */
    private static function file(mixed $path): ?array
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        return [
            'url' => asset('storage/'.$path),
            'name' => basename($path),
            'extension' => strtolower((string) pathinfo($path, PATHINFO_EXTENSION)),
        ];
    }

    private static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
}
