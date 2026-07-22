<?php

namespace Tests\Feature\Property;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Property\Application\PropertyAttributeValue\PropertyAttributeFormSchemaService;
use Modules\Property\Application\PropertyAttributeValue\PropertyAttributeValueSyncService;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;
use Modules\Property\Models\PropertyAttributeOption;
use Modules\Property\Models\PropertyAttributeValue;
use Modules\Property\Models\PropertyType;
use Tests\TestCase;

class PropertyAttributeValueSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_normalizes_and_synchronizes_every_non_media_type(): void
    {
        $property = $this->property();
        $attributes = collect([
            'text' => $this->attribute('text', AttributeType::Text),
            'textarea' => $this->attribute('textarea', AttributeType::Textarea),
            'number' => $this->attribute('number', AttributeType::Number),
            'price' => $this->attribute('price', AttributeType::Price),
            'boolean' => $this->attribute('boolean', AttributeType::Boolean),
            'radio' => $this->attribute('radio', AttributeType::Radio),
            'select' => $this->attribute('select', AttributeType::Select),
            'checkbox' => $this->attribute('checkbox', AttributeType::Checkbox),
            'multiselect' => $this->attribute('multiselect', AttributeType::Multiselect),
            'date' => $this->attribute('date', AttributeType::Date),
            'datetime' => $this->attribute('datetime', AttributeType::Datetime),
        ]);
        $options = $attributes->filter(fn (PropertyAttribute $attribute) => $attribute->type->hasOptions())
            ->mapWithKeys(fn (PropertyAttribute $attribute) => [
                $attribute->code => [
                    $this->option($attribute),
                    $this->option($attribute),
                ],
            ]);

        $service = app(PropertyAttributeValueSyncService::class);
        $service->synchronize(new Request([
            'attributes' => [
                'text' => '  Penthouse  ',
                'textarea' => "  Long\ncopy  ",
                'number' => '12.500',
                'price' => '150000.25',
                'boolean' => '0',
                'radio' => (string) $options['radio'][0]->id,
                'select' => $options['select'][1]->id,
                'checkbox' => [$options['checkbox'][1]->id, (string) $options['checkbox'][0]->id, $options['checkbox'][1]->id],
                'multiselect' => [$options['multiselect'][0]->id, $options['multiselect'][0]->id],
                'date' => '2026-07-21',
                'datetime' => '2026-07-21 14:35:00',
            ],
        ]), $property, true);

        $values = PropertyAttributeValue::query()->where('property_id', $property->id)
            ->get()->keyBy('attribute_id');

        $this->assertSame('Penthouse', $values[$attributes['text']->id]->text_value);
        $this->assertSame("Long\ncopy", $values[$attributes['textarea']->id]->text_value);
        $this->assertSame('12.500000', $values[$attributes['number']->id]->decimal_value);
        $this->assertSame('150000.250000', $values[$attributes['price']->id]->decimal_value);
        $this->assertFalse($values[$attributes['boolean']->id]->boolean_value);
        $this->assertSame($options['radio'][0]->id, $values[$attributes['radio']->id]->integer_value);
        $this->assertSame($options['select'][1]->id, $values[$attributes['select']->id]->integer_value);
        $this->assertSame(
            [$options['checkbox'][1]->id, $options['checkbox'][0]->id],
            $values[$attributes['checkbox']->id]->json_value
        );
        $this->assertSame([$options['multiselect'][0]->id], $values[$attributes['multiselect']->id]->json_value);
        $this->assertSame('2026-07-21', $values[$attributes['date']->id]->date_value->format('Y-m-d'));
        $this->assertSame('2026-07-21 14:35:00', $values[$attributes['datetime']->id]->datetime_value->format('Y-m-d H:i:s'));

        foreach ($attributes as $attribute) {
            $value = $values[$attribute->id];
            foreach (['text_value', 'decimal_value', 'boolean_value', 'integer_value', 'date_value', 'datetime_value', 'json_value'] as $column) {
                if ($column !== $attribute->type->valueColumn()) {
                    $this->assertNull($value->{$column}, "{$attribute->code} did not clear {$column}");
                }
            }
        }
    }

    public function test_datetime_local_values_are_normalized_with_optional_seconds(): void
    {
        $property = $this->property();
        $attribute = $this->attribute('available_at', AttributeType::Datetime);
        $service = app(PropertyAttributeValueSyncService::class);

        $service->synchronize(new Request([
            'attributes' => ['available_at' => '2026-07-21T14:35'],
        ]), $property);

        $this->assertDatabaseHas('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $attribute->id,
            'datetime_value' => '2026-07-21 14:35:00',
        ]);

        $service->synchronize(new Request([
            'attributes' => ['available_at' => '2026-07-21T14:35:42'],
        ]), $property);

        $this->assertDatabaseHas('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $attribute->id,
            'datetime_value' => '2026-07-21 14:35:42',
        ]);
    }

    public function test_validation_enforces_required_metadata_options_and_uniqueness(): void
    {
        $property = $this->property();
        $other = $this->property();
        $required = $this->attribute('required_text', AttributeType::Text, [
            'is_required' => true,
            'validation' => 'alpha_num',
            'regex' => '/^[A-Z0-9]+$/',
        ]);
        $unique = $this->attribute('unique_number', AttributeType::Number, ['is_unique' => true]);
        $uniqueMulti = $this->attribute('unique_multi', AttributeType::Multiselect, ['is_unique' => true]);
        $multiA = $this->option($uniqueMulti);
        $multiB = $this->option($uniqueMulti);
        $select = $this->attribute('select_code', AttributeType::Select);
        $foreign = $this->option($this->attribute('foreign', AttributeType::Select));
        $uniqueNumber = '42.000000';
        $uniqueMultiValue = [$multiA->id, $multiB->id];
        PropertyAttributeValue::factory()->create([
            'property_id' => $other->id,
            'attribute_id' => $unique->id,
            'decimal_value' => $uniqueNumber,
            'unique_hash' => hash(
                'sha256',
                AttributeType::Number->value."\0".json_encode(
                    $uniqueNumber,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
                )
            ),
        ]);
        $sortedMulti = $uniqueMultiValue;
        sort($sortedMulti, SORT_REGULAR);
        PropertyAttributeValue::factory()->create([
            'property_id' => $other->id,
            'attribute_id' => $uniqueMulti->id,
            'json_value' => $uniqueMultiValue,
            'unique_hash' => hash(
                'sha256',
                AttributeType::Multiselect->value."\0".json_encode(
                    array_values($sortedMulti),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
                )
            ),
        ]);

        try {
            app(PropertyAttributeValueSyncService::class)->synchronize(new Request([
                'attributes' => [
                    'required_text' => 'bad value',
                    'unique_number' => '42.000',
                    'unique_multi' => [$multiA->id, $multiA->id, $multiB->id],
                    'select_code' => $foreign->id,
                ],
            ]), $property, false);
            $this->fail('Validation should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('attributes.required_text', $exception->errors());
            $this->assertArrayHasKey('attributes.unique_number', $exception->errors());
            $this->assertArrayHasKey('attributes.unique_multi', $exception->errors());
            $this->assertArrayHasKey('attributes.select_code', $exception->errors());
        }

        $this->assertDatabaseMissing('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $required->id,
        ]);
    }

    public function test_inactive_existing_option_can_be_retained_but_not_newly_selected(): void
    {
        $property = $this->property();
        $other = $this->property();
        $attribute = $this->attribute('features', AttributeType::Multiselect);
        $inactive = $this->option($attribute, false);
        PropertyAttributeValue::factory()->create([
            'property_id' => $property->id,
            'attribute_id' => $attribute->id,
            'json_value' => [$inactive->id],
        ]);

        app(PropertyAttributeValueSyncService::class)->synchronize(new Request([
            'attributes' => ['features' => [$inactive->id]],
        ]), $property);

        $this->expectException(ValidationException::class);
        app(PropertyAttributeValueSyncService::class)->synchronize(new Request([
            'attributes' => ['features' => [$inactive->id]],
        ]), $other);
    }

    public function test_inactive_and_unassigned_attribute_values_are_preserved(): void
    {
        $property = $this->property();
        $active = $this->attribute('active', AttributeType::Text);
        $inactive = $this->attribute('inactive', AttributeType::Text, ['is_active' => false]);
        $unassigned = PropertyAttribute::factory()->create([
            'code' => 'unassigned',
            'type' => AttributeType::Text,
        ]);
        foreach ([$inactive, $unassigned] as $attribute) {
            PropertyAttributeValue::factory()->create([
                'property_id' => $property->id,
                'attribute_id' => $attribute->id,
                'text_value' => 'preserve me',
            ]);
        }

        app(PropertyAttributeValueSyncService::class)->synchronize(new Request([
            'attributes' => ['active' => 'updated'],
        ]), $property);

        $this->assertDatabaseHas('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $active->id,
            'text_value' => 'updated',
        ]);
        foreach ([$inactive, $unassigned] as $attribute) {
            $this->assertDatabaseHas('property_attribute_values', [
                'property_id' => $property->id,
                'attribute_id' => $attribute->id,
                'text_value' => 'preserve me',
            ]);
        }
    }

    public function test_defaults_apply_only_on_create_and_empty_values_remove_rows(): void
    {
        $property = $this->property();
        $defaulted = $this->attribute('defaulted', AttributeType::Text, [
            'default_value' => ['value' => 'Default copy'],
        ]);
        $boolean = $this->attribute('flag', AttributeType::Boolean, [
            'default_value' => ['value' => false],
        ]);

        app(PropertyAttributeValueSyncService::class)->synchronize(new Request, $property, true);
        $this->assertDatabaseHas('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $defaulted->id,
            'text_value' => 'Default copy',
        ]);
        $this->assertDatabaseHas('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $boolean->id,
            'boolean_value' => false,
        ]);

        app(PropertyAttributeValueSyncService::class)->synchronize(new Request([
            'attributes' => ['defaulted' => ''],
        ]), $property);
        $this->assertDatabaseMissing('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $defaulted->id,
        ]);
    }

    public function test_empty_option_set_form_sentinels_clear_checkbox_and_multiselect_values(): void
    {
        $property = $this->property();
        $checkbox = $this->attribute('checkboxes', AttributeType::Checkbox);
        $multiselect = $this->attribute('multiple', AttributeType::Multiselect);
        PropertyAttributeValue::factory()->create([
            'property_id' => $property->id,
            'attribute_id' => $checkbox->id,
            'json_value' => [$this->option($checkbox)->id],
        ]);
        PropertyAttributeValue::factory()->create([
            'property_id' => $property->id,
            'attribute_id' => $multiselect->id,
            'json_value' => [$this->option($multiselect)->id],
        ]);

        app(PropertyAttributeValueSyncService::class)->synchronize(new Request([
            'attributes_present' => [
                'checkboxes' => '1',
                'multiple' => '1',
            ],
        ]), $property);

        $this->assertDatabaseMissing('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $checkbox->id,
        ]);
        $this->assertDatabaseMissing('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $multiselect->id,
        ]);
    }

    public function test_media_upload_replace_remove_gallery_order_finalize_and_rollback(): void
    {
        Storage::fake('public');
        $property = $this->property();
        $image = $this->attribute('hero', AttributeType::Image);
        $file = $this->attribute('brochure', AttributeType::File);
        $gallery = $this->attribute('gallery', AttributeType::Gallery);
        $oldImage = "properties/attributes/{$property->id}/hero/old.jpg";
        $oldGallery = "properties/attributes/{$property->id}/gallery/old.jpg";
        Storage::disk('public')->put($oldImage, 'old');
        Storage::disk('public')->put($oldGallery, 'old');
        Storage::disk('public')->put('media-library/shared.jpg', 'shared');
        PropertyAttributeValue::factory()->create([
            'property_id' => $property->id,
            'attribute_id' => $image->id,
            'text_value' => $oldImage,
        ]);
        PropertyAttributeValue::factory()->create([
            'property_id' => $property->id,
            'attribute_id' => $gallery->id,
            'json_value' => [$oldGallery, 'media-library/shared.jpg'],
        ]);

        $request = Request::create(
            '/',
            'POST',
            [
                'attribute_gallery_existing' => [
                    'gallery' => ['media-library/shared.jpg', $oldGallery],
                ],
            ],
            [],
            [
                'attributes' => [
                    'hero' => UploadedFile::fake()->image('new.jpg'),
                    'brochure' => UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf'),
                    'gallery' => [
                        UploadedFile::fake()->image('one.jpg'),
                        UploadedFile::fake()->image('two.jpg'),
                    ],
                ],
            ],
        );

        $changes = app(PropertyAttributeValueSyncService::class)->synchronize($request, $property);
        $storedImage = PropertyAttributeValue::query()
            ->whereBelongsTo($property)->where('attribute_id', $image->id)->value('text_value');
        $storedFile = PropertyAttributeValue::query()
            ->whereBelongsTo($property)->where('attribute_id', $file->id)->value('text_value');
        $storedGallery = PropertyAttributeValue::query()
            ->whereBelongsTo($property)->where('attribute_id', $gallery->id)->firstOrFail()->json_value;

        Storage::disk('public')->assertExists($storedImage);
        Storage::disk('public')->assertExists($storedFile);
        $this->assertSame(['media-library/shared.jpg', $oldGallery], array_slice($storedGallery, 0, 2));
        Storage::disk('public')->assertExists($storedGallery[2]);
        Storage::disk('public')->assertExists($storedGallery[3]);

        $changes->finalize();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($oldGallery);
        Storage::disk('public')->assertExists('media-library/shared.jpg');

        $remove = app(PropertyAttributeValueSyncService::class)->synchronize(new Request([
            'attributes_remove' => ['hero' => '1'],
            'attribute_gallery_existing' => ['gallery' => []],
        ]), $property);
        $remove->rollback();
        Storage::disk('public')->assertExists($storedImage);
        foreach (array_slice($storedGallery, 2) as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_required_media_accounts_for_retained_files_and_upload_limits_are_enforced(): void
    {
        Storage::fake('public');
        $property = $this->property();
        $image = $this->attribute('required_image', AttributeType::Image, ['is_required' => true]);
        $this->attribute('gallery_limit', AttributeType::Gallery);
        $this->attribute('gallery_size', AttributeType::Gallery);
        $this->attribute('image_limit', AttributeType::Image);
        $this->attribute('file_limit', AttributeType::File);
        $old = "properties/attributes/{$property->id}/required_image/old.jpg";
        Storage::disk('public')->put($old, 'old');
        PropertyAttributeValue::factory()->create([
            'property_id' => $property->id,
            'attribute_id' => $image->id,
            'text_value' => $old,
        ]);

        app(PropertyAttributeValueSyncService::class)->synchronize(new Request, $property);

        $request = Request::create(
            '/',
            'POST',
            ['attributes_remove' => ['required_image' => '1']],
            [],
            [
                'attributes' => [
                    'gallery_limit' => array_map(
                        fn (int $index) => UploadedFile::fake()->image("gallery-{$index}.jpg"),
                        range(1, 21)
                    ),
                    'gallery_size' => [UploadedFile::fake()->image('large-gallery.jpg')->size(4097)],
                    'image_limit' => UploadedFile::fake()->image('large-image.jpg')->size(4097),
                    'file_limit' => UploadedFile::fake()->create('large.bin', 10_241),
                ],
            ],
        );

        try {
            app(PropertyAttributeValueSyncService::class)->synchronize($request, $property);
            $this->fail('Media validation should fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('attributes.required_image', $exception->errors());
            $this->assertArrayHasKey('attributes.gallery_limit', $exception->errors());
            $this->assertArrayHasKey('attributes.gallery_size.0', $exception->errors());
            $this->assertArrayHasKey('attributes.image_limit', $exception->errors());
            $this->assertArrayHasKey('attributes.file_limit', $exception->errors());
        }
    }

    public function test_local_upload_exception_rolls_back_files_already_stored(): void
    {
        Storage::fake('public');
        $property = $this->property();
        $this->attribute('hero', AttributeType::Image);
        $this->attribute('gallery', AttributeType::Gallery);

        $sourceImage = UploadedFile::fake()->image('source.jpg');
        $failingUpload = new class($sourceImage->getPathname(), 'missing.jpg', 'image/jpeg', UPLOAD_ERR_OK, true) extends UploadedFile
        {
            public function storeAs($path, $name = null, $options = []): string|false
            {
                throw new \RuntimeException('Simulated storage failure');
            }
        };

        $request = Request::create(
            '/',
            'POST',
            [],
            [],
            [
                'attributes' => [
                    'hero' => UploadedFile::fake()->image('new.jpg'),
                    'gallery' => [$failingUpload],
                ],
            ],
        );

        try {
            app(PropertyAttributeValueSyncService::class)->synchronize($request, $property);
            $this->fail('Storage exception should bubble.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated storage failure', $exception->getMessage());
        }

        $this->assertSame([], Storage::disk('public')->allFiles("properties/attributes/{$property->id}"));
    }

    public function test_form_schema_hydrates_existing_values_and_active_groups_only(): void
    {
        $property = $this->property();
        $attribute = $this->attribute('title_copy', AttributeType::Text);
        PropertyAttributeValue::factory()->create([
            'property_id' => $property->id,
            'attribute_id' => $attribute->id,
            'text_value' => 'Existing',
        ]);

        $schema = app(PropertyAttributeFormSchemaService::class)->forProperty($property);

        $this->assertCount(1, $schema);
        $this->assertSame('Existing', $schema->first()['attributes']->first()['value']);
    }

    private function attribute(string $code, AttributeType $type, array $overrides = []): PropertyAttribute
    {
        $attribute = PropertyAttribute::factory()->create(array_merge([
            'code' => $code,
            'name' => ['en' => ucfirst(str_replace('_', ' ', $code))],
            'type' => $type,
        ], $overrides));
        $group = PropertyAttributeGroup::query()->firstOrCreate(
            ['position' => 0],
            ['name' => ['en' => 'Details'], 'is_active' => true]
        );
        $group->attributes()->attach($attribute->id, ['position' => $group->attributes()->count()]);

        return $attribute;
    }

    private function option(PropertyAttribute $attribute, bool $active = true): PropertyAttributeOption
    {
        return PropertyAttributeOption::factory()->create([
            'attribute_id' => $attribute->id,
            'is_active' => $active,
        ]);
    }

    private function property(): Property
    {
        $location = Location::query()->firstOrCreate(
            ['name->en' => 'Test area'],
            ['name' => ['en' => 'Test area'], 'type' => LocationType::Area]
        );
        $type = PropertyType::query()->first() ?? PropertyType::factory()->create();

        return Property::factory()->create([
            'location_id' => $location->id,
            'property_type_id' => $type->id,
        ]);
    }
}
