<?php

namespace Modules\Property\Application\PropertyAttributeValue;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeValue;
use Throwable;

final class PropertyAttributeValueSyncService
{
    private const FILE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip'];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp'];

    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/avif',
        'image/bmp',
        'image/x-ms-bmp',
    ];

    private const FILE_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain',
        'application/zip',
        'application/x-zip-compressed',
    ];

    private const VALUE_COLUMNS = [
        'text_value',
        'decimal_value',
        'boolean_value',
        'integer_value',
        'date_value',
        'datetime_value',
        'json_value',
    ];

    public function synchronize(
        Request $request,
        Property $property,
        bool $creating = false,
        ?array $groupIds = null,
    ): PropertyAttributeMediaChanges {
        $groupIds ??= $property->attributeGroups()
            ->pluck('property_attribute_groups.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $attributes = $this->participatingAttributes($groupIds);
        $existing = $this->existingValues($property);
        $validator = $this->validator($request, $property, $attributes, $existing, $creating);
        $validator->validate();

        $changes = new PropertyAttributeMediaChanges(
            Storage::disk('public'),
            "properties/attributes/{$property->getKey()}",
        );

        try {
            $normalized = $attributes->mapWithKeys(fn (PropertyAttribute $attribute): array => [
                $attribute->id => [
                    'attribute' => $attribute,
                    'value' => $this->normalize(
                        $request,
                        $attribute,
                        $existing->get($attribute->id),
                        $creating,
                        true,
                        $property,
                        $changes,
                    ),
                ],
            ]);

            try {
                DB::transaction(function () use ($normalized, $property, $attributes): void {
                    foreach ($normalized as $item) {
                        $this->persist($property, $item['attribute'], $item['value']);
                    }

                    $keepIds = $attributes->pluck('id')->all();
                    $orphanQuery = $property->attributeValues();
                    if ($keepIds === []) {
                        $orphanQuery->delete();
                    } else {
                        $orphanQuery->whereNotIn('attribute_id', $keepIds)->delete();
                    }
                });
            } catch (UniqueConstraintViolationException) {
                throw ValidationException::withMessages([
                    'attributes' => __('One or more unique property attributes have already been taken.'),
                ]);
            }

            return $changes;
        } catch (Throwable $exception) {
            $changes->rollback();

            throw $exception;
        }
    }

    public function validate(
        Request $request,
        ?Property $property = null,
        bool $creating = false,
        ?array $groupIds = null,
    ): void {
        $groupIds ??= $property === null
            ? []
            : $property->attributeGroups()
                ->pluck('property_attribute_groups.id')
                ->map(fn ($id) => (int) $id)
                ->all();
        $attributes = $this->participatingAttributes($groupIds);
        $this->validator(
            $request,
            $property,
            $attributes,
            $this->existingValues($property),
            $creating,
        )->validate();
    }

    /**
     * @param  Collection<int, PropertyAttribute>  $attributes
     * @param  Collection<int, PropertyAttributeValue>  $existing
     */
    private function validator(
        Request $request,
        ?Property $property,
        Collection $attributes,
        Collection $existing,
        bool $creating,
    ): ValidatorContract {
        $rules = [
            'attributes' => ['nullable', 'array'],
            'attributes_present' => ['nullable', 'array'],
            'attributes_remove' => ['nullable', 'array'],
            'attribute_media_path' => ['nullable', 'array'],
            'attribute_gallery_existing' => ['nullable', 'array'],
        ];
        $names = [];

        foreach ($attributes as $attribute) {
            $key = "attributes.{$attribute->code}";
            $names[$key] = $attribute->name;
            $names["{$key}.*"] = $attribute->name;
            $rules += $this->rulesFor($attribute);
        }

        $validator = Validator::make($request->all(), $rules, [], $names);
        $validator->after(function (ValidatorContract $validator) use (
            $request,
            $property,
            $attributes,
            $existing,
            $creating,
        ): void {
            foreach ($attributes as $attribute) {
                $key = "attributes.{$attribute->code}";
                $current = $existing->get($attribute->id);

                $this->validateRetainedMedia($validator, $request, $attribute, $current);
                $this->validateSelectedMedia($validator, $request, $attribute, $current);
                $this->validateGalleryCount($validator, $request, $attribute, $current);
                $normalized = $this->normalize(
                    $request,
                    $attribute,
                    $current,
                    $creating,
                    false,
                );

                if ($attribute->is_required && $this->isEmpty($normalized, $attribute->type)) {
                    $validator->errors()->add($key, __('The :attribute field is required.', [
                        'attribute' => $attribute->name,
                    ]));
                }

                $this->validateOptions($validator, $attribute, $normalized, $current, $key);

                if ($attribute->is_unique
                    && ! $attribute->type->isMedia()
                    && ! $this->isEmpty($normalized, $attribute->type)
                    && $this->duplicateExists($property, $attribute, $normalized)
                ) {
                    $validator->errors()->add($key, __('The :attribute has already been taken.', [
                        'attribute' => $attribute->name,
                    ]));
                }
            }
        });

        return $validator;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rulesFor(PropertyAttribute $attribute): array
    {
        $key = "attributes.{$attribute->code}";
        $rules = match ($attribute->type) {
            AttributeType::Text, AttributeType::Textarea => [$key => ['nullable', 'string']],
            AttributeType::Number, AttributeType::Price => [$key => ['nullable', $this->decimalRule()]],
            AttributeType::Boolean => [$key => ['nullable', 'boolean']],
            AttributeType::Radio, AttributeType::Select => [$key => ['nullable', 'integer']],
            AttributeType::Checkbox, AttributeType::Multiselect => [
                $key => ['nullable', 'array'],
                "{$key}.*" => ['nullable', 'integer'],
            ],
            AttributeType::Date => [$key => ['nullable', 'date_format:Y-m-d']],
            AttributeType::Datetime => [$key => ['nullable', $this->datetimeRule()]],
            AttributeType::Image => [
                $key => ['nullable', 'file', 'max:4096', 'mimes:'.implode(',', self::IMAGE_EXTENSIONS), $this->uploadExtensionRule(self::IMAGE_EXTENSIONS)],
                "attribute_media_path.{$attribute->code}" => ['nullable', 'string', 'max:2048'],
                "attributes_remove.{$attribute->code}" => ['nullable', 'boolean'],
            ],
            AttributeType::File => [
                $key => ['nullable', 'file', 'max:10240', 'mimes:'.implode(',', self::FILE_EXTENSIONS), $this->uploadExtensionRule(self::FILE_EXTENSIONS)],
                "attribute_media_path.{$attribute->code}" => ['nullable', 'string', 'max:2048'],
                "attributes_remove.{$attribute->code}" => ['nullable', 'boolean'],
            ],
            AttributeType::Gallery => [
                $key => ['nullable', 'array', 'max:20'],
                "{$key}.*" => ['file', 'max:4096', 'mimes:'.implode(',', self::IMAGE_EXTENSIONS), $this->uploadExtensionRule(self::IMAGE_EXTENSIONS)],
                "attribute_gallery_existing.{$attribute->code}" => ['nullable', 'array', 'max:20'],
                "attribute_gallery_existing.{$attribute->code}.*" => ['string', 'max:2048'],
            ],
        };

        $metadataRule = $this->meaningfulMetadataRule($attribute);
        if ($metadataRule !== null) {
            $rules[$key][] = $metadataRule;
        }
        if ($attribute->regex !== null && in_array($attribute->type, [AttributeType::Text, AttributeType::Textarea], true)) {
            $rules[$key][] = $this->regexRule($attribute->regex);
        }

        return $rules;
    }

    private function meaningfulMetadataRule(PropertyAttribute $attribute): ?string
    {
        if ($attribute->validation === null) {
            return null;
        }

        return match ($attribute->type) {
            AttributeType::Text, AttributeType::Textarea => in_array(
                $attribute->validation,
                ['email', 'url', 'alpha', 'alpha_num'],
                true
            ) ? $attribute->validation : null,
            AttributeType::Number, AttributeType::Price => in_array(
                $attribute->validation,
                ['integer', 'numeric'],
                true
            ) ? $attribute->validation : null,
            default => null,
        };
    }

    private function normalize(
        Request $request,
        PropertyAttribute $attribute,
        ?PropertyAttributeValue $current,
        bool $creating,
        bool $storeUploads,
        ?Property $property = null,
        ?PropertyAttributeMediaChanges $changes = null,
    ): mixed {
        $code = $attribute->code;
        $submitted = $request->exists("attributes.{$code}")
            || $request->hasFile("attributes.{$code}")
            || $request->boolean("attributes_present.{$code}");
        $raw = $request->input("attributes.{$code}");

        if (! $submitted && $creating && ! $attribute->type->isMedia() && $attribute->default_value !== null) {
            $default = data_get($attribute->default_value, 'value');
            if ($this->defaultIsSensible($attribute, $default)) {
                $raw = $default;
                $submitted = true;
            }
        }

        return match ($attribute->type) {
            AttributeType::Text, AttributeType::Textarea => $submitted ? $this->trimmed($raw) : null,
            AttributeType::Number, AttributeType::Price => $submitted && $this->isValidDecimal($raw)
                ? $this->decimal($raw)
                : null,
            AttributeType::Boolean => $submitted && $this->isBooleanLike($raw)
                ? filter_var($raw, FILTER_VALIDATE_BOOLEAN)
                : null,
            AttributeType::Radio, AttributeType::Select => $submitted && filter_var($raw, FILTER_VALIDATE_INT) !== false
                ? (int) $raw
                : null,
            AttributeType::Checkbox, AttributeType::Multiselect => $submitted
                ? $this->orderedUniqueIds(is_array($raw) ? $raw : [])
                : null,
            AttributeType::Date => $submitted ? $this->date($raw, 'Y-m-d') : null,
            AttributeType::Datetime => $submitted ? $this->datetime($raw) : null,
            AttributeType::Image, AttributeType::File => $this->normalizeSingleMedia(
                $request,
                $attribute,
                $current,
                $storeUploads,
                $property,
                $changes,
            ),
            AttributeType::Gallery => $this->normalizeGallery(
                $request,
                $attribute,
                $current,
                $storeUploads,
                $property,
                $changes,
            ),
        };
    }

    private function normalizeSingleMedia(
        Request $request,
        PropertyAttribute $attribute,
        ?PropertyAttributeValue $current,
        bool $storeUploads,
        ?Property $property,
        ?PropertyAttributeMediaChanges $changes,
    ): mixed {
        $code = $attribute->code;
        $upload = $request->file("attributes.{$code}");
        $selected = $this->trimmed($request->input("attribute_media_path.{$code}"));
        $remove = $request->boolean("attributes_remove.{$code}");
        $old = $current?->text_value;

        if ($upload instanceof UploadedFile) {
            if (! $storeUploads) {
                return '__pending_upload__';
            }

            $path = $this->store($upload, $property, $attribute, $changes);
            if ($old !== null && $old !== $path) {
                $changes?->trackSuperseded($old);
            }

            return $path;
        }

        if ($selected !== null) {
            if ($old !== null && $old !== $selected) {
                $changes?->trackSuperseded($old);
            }

            return $selected;
        }

        if ($remove) {
            if ($old !== null) {
                $changes?->trackSuperseded($old);
            }

            return null;
        }

        return $old;
    }

    /**
     * @return list<string>
     */
    private function normalizeGallery(
        Request $request,
        PropertyAttribute $attribute,
        ?PropertyAttributeValue $current,
        bool $storeUploads,
        ?Property $property,
        ?PropertyAttributeMediaChanges $changes,
    ): array {
        $code = $attribute->code;
        $old = array_values(array_filter($current?->json_value ?? [], 'is_string'));
        $hasRetainedInput = Arr::has($request->all(), "attribute_gallery_existing.{$code}");
        $retainedInput = $request->input("attribute_gallery_existing.{$code}", []);
        $retained = $hasRetainedInput && is_array($retainedInput)
            ? array_values($retainedInput)
            : ($hasRetainedInput ? [] : $old);
        $uploads = $request->file("attributes.{$code}", []);
        $uploads = is_array($uploads) ? $uploads : [];

        $new = $retained;
        foreach ($uploads as $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }
            $new[] = $storeUploads
                ? $this->store($upload, $property, $attribute, $changes)
                : '__pending_upload__';
        }

        if ($storeUploads) {
            foreach (array_diff($old, $new) as $path) {
                $changes?->trackSuperseded($path);
            }
        }

        return $new;
    }

    private function store(
        UploadedFile $upload,
        ?Property $property,
        PropertyAttribute $attribute,
        ?PropertyAttributeMediaChanges $changes,
    ): string {
        $allowed = $attribute->type === AttributeType::File
            ? self::FILE_EXTENSIONS
            : self::IMAGE_EXTENSIONS;
        $extension = strtolower((string) $upload->extension());
        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                "attributes.{$attribute->code}" => __('The uploaded file type is not allowed.'),
            ]);
        }

        $filename = (string) Str::uuid().".{$extension}";
        $directory = "properties/attributes/{$property?->getKey()}/{$attribute->code}";
        $path = $upload->storeAs($directory, $filename, 'public');
        if (! is_string($path) || $path === '') {
            throw new \RuntimeException("Unable to store property attribute upload [{$attribute->code}].");
        }
        $changes?->trackNew($path);

        return $path;
    }

    private function validateSelectedMedia(
        ValidatorContract $validator,
        Request $request,
        PropertyAttribute $attribute,
        ?PropertyAttributeValue $current,
    ): void {
        if (! in_array($attribute->type, [AttributeType::Image, AttributeType::File], true)) {
            return;
        }

        $key = "attribute_media_path.{$attribute->code}";
        $path = $this->trimmed($request->input($key));
        if ($path === null || $path === $current?->text_value) {
            return;
        }

        if (! $this->isSafeLibraryPath($path)) {
            $validator->errors()->add($key, __('The selected media file is invalid.'));

            return;
        }

        $extensions = $attribute->type === AttributeType::Image
            ? self::IMAGE_EXTENSIONS
            : self::FILE_EXTENSIONS;
        $mimeTypes = $attribute->type === AttributeType::Image
            ? self::IMAGE_MIME_TYPES
            : self::FILE_MIME_TYPES;
        $disk = Storage::disk('public');
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        try {
            $exists = $disk->exists($path);
        } catch (Throwable) {
            $exists = false;
        }

        if (! in_array($extension, $extensions, true) || ! $exists) {
            $validator->errors()->add($key, __('The selected media file is invalid.'));

            return;
        }

        $mime = $this->libraryFileMime($disk, $path);
        if ($mime === null || ! in_array($mime, $mimeTypes, true)) {
            $validator->errors()->add($key, __('The selected media file is invalid.'));
        }
    }

    private function validateRetainedMedia(
        ValidatorContract $validator,
        Request $request,
        PropertyAttribute $attribute,
        ?PropertyAttributeValue $current,
    ): void {
        if ($attribute->type !== AttributeType::Gallery
            || ! Arr::has($request->all(), "attribute_gallery_existing.{$attribute->code}")
        ) {
            return;
        }

        $old = $current?->json_value ?? [];
        $retained = $request->input("attribute_gallery_existing.{$attribute->code}", []);
        if (! is_array($retained)) {
            return;
        }

        foreach ($retained as $path) {
            if (! is_string($path) || ! in_array($path, $old, true)) {
                $validator->errors()->add(
                    "attribute_gallery_existing.{$attribute->code}",
                    __('The retained media selection is invalid.')
                );
                break;
            }
        }
    }

    private function isSafeLibraryPath(string $path): bool
    {
        $normalized = str_replace('\\', '/', trim($path));

        return $normalized !== ''
            && $normalized === ltrim($normalized, '/')
            && str_starts_with($normalized, 'media-library/')
            && ! str_contains($normalized, '../')
            && ! str_contains($normalized, '/..')
            && ! str_contains($normalized, '://')
            && ! str_starts_with($normalized, '//')
            && preg_match('/^[A-Za-z0-9._\/ -]+$/', $normalized) === 1;
    }

    private function libraryFileMime(Filesystem $disk, string $path): ?string
    {
        $candidates = [];

        try {
            $mime = $disk->mimeType($path);
            if (is_string($mime) && $mime !== '') {
                $candidates[] = strtolower(trim(explode(';', $mime, 2)[0]));
            }
        } catch (Throwable) {
            // Fall through to local finfo when the driver cannot resolve metadata.
        }

        try {
            $fullPath = $disk->path($path);
            if (is_string($fullPath) && $fullPath !== '' && is_file($fullPath)) {
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($fullPath);
                if (is_string($mime) && $mime !== '') {
                    $candidates[] = strtolower(trim(explode(';', $mime, 2)[0]));
                }
            }
        } catch (Throwable) {
            // Prefer any previously resolved candidate.
        }

        foreach ($candidates as $mime) {
            if (! in_array($mime, ['application/octet-stream', 'inode/x-empty', 'application/x-empty'], true)) {
                return $mime;
            }
        }

        return $candidates[0] ?? null;
    }

    private function validateGalleryCount(
        ValidatorContract $validator,
        Request $request,
        PropertyAttribute $attribute,
        ?PropertyAttributeValue $current,
    ): void {
        if ($attribute->type !== AttributeType::Gallery) {
            return;
        }

        $code = $attribute->code;
        $retainedInput = $request->input("attribute_gallery_existing.{$code}");
        $retainedCount = is_array($retainedInput)
            ? count($retainedInput)
            : count($current?->json_value ?? []);
        $uploads = $request->file("attributes.{$code}", []);
        $uploadCount = is_array($uploads) ? count($uploads) : 0;

        if ($retainedCount + $uploadCount > 20) {
            $validator->errors()->add(
                "attributes.{$code}",
                __('The :attribute may not have more than :max items.', [
                    'attribute' => $attribute->name,
                    'max' => 20,
                ])
            );
        }
    }

    private function validateOptions(
        ValidatorContract $validator,
        PropertyAttribute $attribute,
        mixed $normalized,
        ?PropertyAttributeValue $current,
        string $key,
    ): void {
        if (! $attribute->type->hasOptions() || $normalized === null) {
            return;
        }

        $ids = is_array($normalized) ? $normalized : [$normalized];
        $currentIds = $attribute->type->isMultiple()
            ? ($current?->json_value ?? [])
            : array_filter([$current?->integer_value]);
        $valid = $attribute->options
            ->filter(fn ($option) => $option->is_active || in_array($option->id, $currentIds, true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (array_diff($ids, $valid) !== []) {
            $validator->errors()->add($key, __('The selected :attribute is invalid.', [
                'attribute' => $attribute->name,
            ]));
        }
    }

    private function duplicateExists(
        ?Property $property,
        PropertyAttribute $attribute,
        mixed $normalized,
    ): bool {
        $query = PropertyAttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->where('unique_hash', $this->uniqueHash($attribute, $normalized));

        if ($property?->getKey() !== null) {
            $query->where('property_id', '!=', $property->getKey());
        }

        return $query->exists();
    }

    private function persist(Property $property, PropertyAttribute $attribute, mixed $normalized): void
    {
        if ($this->isEmpty($normalized, $attribute->type)) {
            PropertyAttributeValue::query()
                ->where('property_id', $property->getKey())
                ->where('attribute_id', $attribute->id)
                ->delete();

            return;
        }

        $values = array_fill_keys(self::VALUE_COLUMNS, null);
        $values[$attribute->type->valueColumn()] = $normalized;
        $values['unique_hash'] = $attribute->is_unique && ! $attribute->type->isMedia()
            ? $this->uniqueHash($attribute, $normalized)
            : null;

        PropertyAttributeValue::query()->updateOrCreate([
            'property_id' => $property->getKey(),
            'attribute_id' => $attribute->id,
        ], $values);
    }

    private function isEmpty(mixed $value, AttributeType $type): bool
    {
        if ($type === AttributeType::Boolean) {
            return $value === null;
        }

        return $value === null || $value === '' || $value === [];
    }

    private function trimmed(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function decimal(mixed $value): string
    {
        preg_match('/^([+-]?)(\d+)(?:\.(\d{1,6}))?$/', trim((string) $value), $matches);
        $fraction = str_pad($matches[3] ?? '', 6, '0');
        $whole = ltrim($matches[2], '0');
        $whole = $whole === '' ? '0' : $whole;
        $sign = ($matches[1] ?? '') === '-' && ($whole !== '0' || trim($fraction, '0') !== '')
            ? '-'
            : '';

        return $sign.$whole.'.'.$fraction;
    }

    private function date(mixed $value, string $format): ?string
    {
        if (! is_scalar($value) || $value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat($format, (string) $value);

            return $date !== false && $date->format($format) === (string) $value
                ? $date->format($format)
                : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function datetime(mixed $value): ?string
    {
        if (! is_scalar($value) || $value === '') {
            return null;
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d\TH:i', 'Y-m-d\TH:i:s'] as $format) {
            $normalized = $this->date($value, $format);
            if ($normalized !== null) {
                return CarbonImmutable::createFromFormat($format, $normalized)?->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    private function isBooleanLike(mixed $value): bool
    {
        return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true);
    }

    private function defaultIsSensible(PropertyAttribute $attribute, mixed $value): bool
    {
        if ($attribute->type->hasOptions() || $attribute->type->isMedia()) {
            return false;
        }

        $typeIsSensible = match ($attribute->type) {
            AttributeType::Text, AttributeType::Textarea => is_scalar($value),
            AttributeType::Number, AttributeType::Price => $this->isValidDecimal($value),
            AttributeType::Boolean => $this->isBooleanLike($value),
            AttributeType::Date => $this->date($value, 'Y-m-d') !== null,
            AttributeType::Datetime => $this->datetime($value) !== null,
            default => false,
        };

        if (! $typeIsSensible) {
            return false;
        }

        $rules = array_filter([
            $this->meaningfulMetadataRule($attribute),
            $attribute->regex !== null
                && in_array($attribute->type, [AttributeType::Text, AttributeType::Textarea], true)
                    ? $this->regexRule($attribute->regex)
                    : null,
        ]);

        return $rules === [] || ! Validator::make(['value' => $value], ['value' => $rules])->fails();
    }

    private function regexRule(string $pattern): \Closure
    {
        return static function (string $attribute, mixed $value, \Closure $fail) use ($pattern): void {
            if (! is_scalar($value) || preg_match($pattern, (string) $value) !== 1) {
                $fail(__('The :attribute format is invalid.', ['attribute' => $attribute]));
            }
        };
    }

    private function datetimeRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($this->datetime($value) === null) {
                $fail(__('The :attribute field must be a valid date and time.', [
                    'attribute' => $attribute,
                ]));
            }
        };
    }

    private function decimalRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! $this->isValidDecimal($value)) {
                $fail(__('The :attribute must be a decimal with at most 14 integer and 6 fractional digits.', [
                    'attribute' => $attribute,
                ]));
            }
        };
    }

    /**
     * @param  list<string>  $extensions
     */
    private function uploadExtensionRule(array $extensions): \Closure
    {
        return static function (string $attribute, mixed $value, \Closure $fail) use ($extensions): void {
            if ($value instanceof UploadedFile
                && ! in_array(strtolower($value->getClientOriginalExtension()), $extensions, true)
            ) {
                $fail(__('The :attribute file extension is not allowed.', ['attribute' => $attribute]));
            }
        };
    }

    private function isValidDecimal(mixed $value): bool
    {
        if (! is_scalar($value)) {
            return false;
        }

        return preg_match('/^[+-]?\d{1,14}(?:\.\d{1,6})?$/', trim((string) $value)) === 1;
    }

    private function uniqueHash(PropertyAttribute $attribute, mixed $normalized): string
    {
        $canonical = $normalized;
        if (is_array($canonical)) {
            $canonical = array_values($canonical);
            sort($canonical, SORT_REGULAR);
        } elseif (is_bool($canonical)) {
            $canonical = $canonical ? '1' : '0';
        } elseif ($canonical instanceof \DateTimeInterface) {
            $canonical = $canonical->format(DATE_ATOM);
        }

        return hash('sha256', $attribute->type->value."\0".json_encode(
            $canonical,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        ));
    }

    /**
     * @return Collection<int, PropertyAttributeValue>
     */
    private function existingValues(?Property $property): Collection
    {
        if ($property?->getKey() === null) {
            return collect();
        }

        return $property->attributeValues()
            ->get()
            ->keyBy('attribute_id');
    }

    /**
     * @param  array<int, mixed>  $values
     * @return list<int>
     */
    private function orderedUniqueIds(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                continue;
            }

            $id = (int) $value;
            if (! in_array($id, $normalized, true)) {
                $normalized[] = $id;
            }
        }

        return $normalized;
    }

    /**
     * @param  list<int>|null  $groupIds
     * @return Collection<int, PropertyAttribute>
     */
    private function participatingAttributes(?array $groupIds): Collection
    {
        $groupIds = array_values(array_unique(array_filter(
            array_map('intval', $groupIds ?? []),
            static fn (int $id): bool => $id > 0
        )));

        if ($groupIds === []) {
            return collect();
        }

        return PropertyAttribute::query()
            ->active()
            ->whereHas(
                'groups',
                fn ($query) => $query
                    ->whereIn('property_attribute_groups.id', $groupIds)
                    ->where('property_attribute_groups.is_active', true)
            )
            ->with(['options' => fn ($query) => $query->ordered()])
            ->ordered()
            ->get();
    }
}
