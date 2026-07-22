<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;
use Modules\Property\Models\PropertyAttributeOption;
use Modules\Property\Models\PropertyAttributeValue;
use Modules\Property\Models\PropertyType;
use Modules\User\Enums\CmsStatus;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use Throwable;

class PropertyAttributeValueIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Location $district;

    private PropertyType $propertyType;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->admin());
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleCookieRedirect::class,
            LocaleSessionRedirect::class,
        ]);

        $city = Location::query()->create([
            'name' => ['en' => 'Istanbul'],
            'type' => LocationType::City,
        ]);
        $this->district = Location::query()->create([
            'name' => ['en' => 'Kadikoy'],
            'type' => LocationType::Municipality,
            'parent_id' => $city->id,
        ]);
        $this->propertyType = PropertyType::factory()->create();
    }

    public function test_create_route_persists_dynamic_scalar_option_and_media_values(): void
    {
        $text = $this->attribute('view_note', AttributeType::Text);
        $boolean = $this->attribute('furnished', AttributeType::Boolean);
        $select = $this->attribute('heating', AttributeType::Select);
        $heating = $this->option($select, 'Central');
        $multi = $this->attribute('amenities', AttributeType::Multiselect);
        $pool = $this->option($multi, 'Pool');
        $image = $this->attribute('hero_image', AttributeType::Image);
        $gallery = $this->attribute('gallery', AttributeType::Gallery);
        $file = $this->attribute('brochure', AttributeType::File);

        $response = $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-CREATE'), [
            'attributes' => [
                'view_note' => '  Bosphorus view  ',
                'furnished' => '0',
                'heating' => $heating->id,
                'amenities' => [$pool->id],
                'hero_image' => UploadedFile::fake()->image('hero.jpg'),
                'gallery' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.jpg'),
                ],
                'brochure' => UploadedFile::fake()->create('brochure.pdf', 50, 'application/pdf'),
            ],
        ]));

        $response->assertRedirect(route('admin.properties.index'));
        $property = Property::query()->where('project_code', 'EAV-CREATE')->firstOrFail();
        $values = $property->attributeValues()->get()->keyBy('attribute_id');

        $this->assertSame('Bosphorus view', $values[$text->id]->text_value);
        $this->assertFalse($values[$boolean->id]->boolean_value);
        $this->assertSame($heating->id, $values[$select->id]->integer_value);
        $this->assertSame([$pool->id], $values[$multi->id]->json_value);
        Storage::disk('public')->assertExists($values[$image->id]->text_value);
        Storage::disk('public')->assertExists($values[$file->id]->text_value);
        $this->assertCount(2, $values[$gallery->id]->json_value);
        foreach ($values[$gallery->id]->json_value as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_edit_route_hydrates_and_updates_values_while_pruning_out_of_group_values(): void
    {
        $text = $this->attribute('view_note', AttributeType::Text);
        $select = $this->attribute('heating', AttributeType::Select);
        $inactiveOption = $this->option($select, 'Legacy system', false);
        $image = $this->attribute('hero_image', AttributeType::Image);
        $gallery = $this->attribute('gallery', AttributeType::Gallery);
        $inactive = PropertyAttribute::factory()->create([
            'code' => 'retired',
            'name' => ['en' => 'Retired'],
            'type' => AttributeType::Text,
            'is_active' => false,
        ]);
        $unassigned = PropertyAttribute::factory()->create([
            'code' => 'unassigned',
            'name' => ['en' => 'Unassigned'],
            'type' => AttributeType::Text,
        ]);
        $property = $this->property('EAV-EDIT');
        $oldImage = "properties/attributes/{$property->id}/hero_image/old.jpg";
        $oldGallery = "properties/attributes/{$property->id}/gallery/old.jpg";
        Storage::disk('public')->put($oldImage, 'old');
        Storage::disk('public')->put($oldGallery, 'old');

        $this->value($property, $text, ['text_value' => 'Existing note']);
        $this->value($property, $select, ['integer_value' => $inactiveOption->id]);
        $this->value($property, $image, ['text_value' => $oldImage]);
        $this->value($property, $gallery, ['json_value' => [$oldGallery]]);
        $this->value($property, $inactive, ['text_value' => 'Keep inactive']);
        $this->value($property, $unassigned, ['text_value' => 'Keep unassigned']);

        $this->get(route('admin.properties.edit', $property))
            ->assertOk()
            ->assertSee('Existing note')
            ->assertSee('Legacy system')
            ->assertSee($oldGallery);

        $response = $this->put(route('admin.properties.update', $property), array_merge($this->payload('EAV-EDIT'), [
            'attributes' => [
                'view_note' => 'Updated note',
                'heating' => $inactiveOption->id,
                'hero_image' => UploadedFile::fake()->image('replacement.jpg'),
                'gallery' => [UploadedFile::fake()->image('new-gallery.jpg')],
            ],
            'attribute_gallery_existing' => [
                'gallery' => [$oldGallery],
            ],
        ]));

        $response->assertRedirect(route('admin.properties.index'));
        $values = $property->fresh()->attributeValues()->get()->keyBy('attribute_id');
        $this->assertSame('Updated note', $values[$text->id]->text_value);
        $this->assertSame($inactiveOption->id, $values[$select->id]->integer_value);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($values[$image->id]->text_value);
        $this->assertSame($oldGallery, $values[$gallery->id]->json_value[0]);
        Storage::disk('public')->assertExists($values[$gallery->id]->json_value[1]);
        $this->assertArrayNotHasKey($inactive->id, $values->all());
        $this->assertArrayNotHasKey($unassigned->id, $values->all());
    }

    public function test_dynamic_validation_redirects_with_the_translated_attribute_label(): void
    {
        $this->attribute('license_number', AttributeType::Text, [
            'name' => ['en' => 'License number'],
            'is_required' => true,
        ]);

        $response = $this->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), $this->payload('EAV-INVALID'))
            ->assertRedirect(route('admin.properties.create'))
            ->assertSessionHasErrors('attributes.license_number');

        $this->assertStringContainsString(
            'License number',
            $response->getSession()->get('errors')->first('attributes.license_number')
        );
        $this->assertDatabaseMissing('properties', ['project_code' => 'EAV-INVALID']);
    }

    public function test_invalid_eav_does_not_mutate_thumbnail_files_on_create_or_update(): void
    {
        $this->attribute('license_number', AttributeType::Text, ['is_required' => true]);

        $this->post(route('admin.properties.store'), $this->payload('EAV-NO-ORPHAN'))
            ->assertSessionHasErrors('attributes.license_number');
        $this->assertSame([], Storage::disk('public')->allFiles('properties/thumbnails'));

        $property = $this->property('EAV-KEEP-THUMB');
        $oldThumbnail = 'properties/thumbnails/existing.jpg';
        Storage::disk('public')->put($oldThumbnail, 'existing');
        $property->update(['thumbnail' => $oldThumbnail]);

        $updatePayload = $this->payload('EAV-KEEP-THUMB');
        $updatePayload['thumbnail_media_path'] = $oldThumbnail;

        $this->put(route('admin.properties.update', $property), $updatePayload)
            ->assertSessionHasErrors('attributes.license_number');

        Storage::disk('public')->assertExists($oldThumbnail);
        $this->assertSame([$oldThumbnail], Storage::disk('public')->allFiles('properties/thumbnails'));
    }

    public function test_file_attributes_reject_unsafe_extensions_and_accept_pdf(): void
    {
        $this->attribute('document', AttributeType::File);

        foreach ([
            ['payload.php', 'application/x-httpd-php'],
            ['page.html', 'text/html'],
            ['vector.svg', 'image/svg+xml'],
        ] as [$name, $mime]) {
            $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-'.Str::upper(pathinfo($name, PATHINFO_FILENAME))), [
                'attributes' => ['document' => UploadedFile::fake()->create($name, 10, $mime)],
            ]))->assertSessionHasErrors('attributes.document');
        }

        $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-PDF'), [
            'attributes' => [
                'document' => UploadedFile::fake()->create('brochure.pdf', 10, 'application/pdf'),
            ],
        ]))->assertRedirect(route('admin.properties.index'));

        $value = Property::query()->where('project_code', 'EAV-PDF')->firstOrFail()
            ->attributeValues()->firstOrFail();
        $this->assertStringEndsWith('.pdf', $value->text_value);
        $this->assertStringNotContainsString('brochure', $value->text_value);
    }

    public function test_media_library_selections_must_be_safe_existing_and_type_compatible(): void
    {
        $this->attribute('hero_image', AttributeType::Image);
        // Minimal 1x1 JPEG so MIME sniffing is stable across suite order and OS drivers.
        $jpeg = hex2bin(
            'ffd8ffe000104a46494600010101006000600000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffdb0043010909090c0b0c180d0d1832211c213232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232ffc00011080001000103011100021101031101ffc40014000100000000000000000000000000000000ffc40014100100000000000000000000000000000000ffda000c0301000210031000003f00bf80ffd9'
        );
        $this->assertNotFalse($jpeg);
        Storage::disk('public')->put('media-library/photo.jpg', $jpeg);
        Storage::disk('public')->put('media-library/document.pdf', '%PDF-1.4');
        Storage::disk('public')->put('properties/thumbnails/other.jpg', $jpeg);
        Storage::disk('public')->assertExists('media-library/photo.jpg');
        $this->assertSame('image/jpeg', Storage::disk('public')->mimeType('media-library/photo.jpg'));

        foreach (['../secret.jpg', 'https://example.test/photo.jpg', 'media-library/missing.jpg', 'media-library/document.pdf', 'properties/thumbnails/other.jpg'] as $path) {
            $this->from(route('admin.properties.create'))
                ->post(route('admin.properties.store'), array_merge($this->payload('EAV-MEDIA-'.md5($path)), [
                    'attribute_media_path' => ['hero_image' => $path],
                ]))->assertSessionHasErrors('attribute_media_path.hero_image');
        }

        $this->from(route('admin.properties.create'))
            ->post(route('admin.properties.store'), array_merge($this->payload('EAV-MEDIA-OK'), [
                'attribute_media_path' => ['hero_image' => 'media-library/photo.jpg'],
            ]))->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('property_attribute_values', ['text_value' => 'media-library/photo.jpg']);
    }

    public function test_thumbnail_removal_and_library_replacement_cleanup_owned_old_files(): void
    {
        $removed = $this->property('EAV-THUMB-REMOVE');
        $removed->update(['thumbnail' => 'properties/thumbnails/remove.jpg']);
        Storage::disk('public')->put('properties/thumbnails/remove.jpg', 'owned');
        $removePayload = $this->payload('EAV-THUMB-REMOVE');
        unset($removePayload['thumbnail_media_path']);
        $removePayload['thumbnail_remove'] = '1';

        $this->put(route('admin.properties.update', $removed), $removePayload)
            ->assertRedirect(route('admin.properties.index'));
        Storage::disk('public')->assertMissing('properties/thumbnails/remove.jpg');

        $replaced = $this->property('EAV-THUMB-LIBRARY');
        $replaced->update(['thumbnail' => 'properties/thumbnails/replace.jpg']);
        Storage::disk('public')->put('properties/thumbnails/replace.jpg', 'owned');
        Storage::disk('public')->put('media-library/replacement.jpg', $this->tinyJpeg());
        \Modules\Base\Models\Media::query()->create([
            'disk' => 'public',
            'path' => 'media-library/replacement.jpg',
            'name' => 'replacement.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'folder_id' => null,
        ]);
        $replacePayload = $this->payload('EAV-THUMB-LIBRARY');
        unset($replacePayload['thumbnail_media_path']);
        $replacePayload['thumbnail_media_path'] = 'media-library/replacement.jpg';

        $this->put(route('admin.properties.update', $replaced), $replacePayload)
            ->assertRedirect(route('admin.properties.index'));
        Storage::disk('public')->assertMissing('properties/thumbnails/replace.jpg');
        Storage::disk('public')->assertExists('media-library/replacement.jpg');
    }

    public function test_unique_multiselect_is_order_insensitive_and_database_constrained(): void
    {
        $attribute = $this->attribute('amenities', AttributeType::Multiselect, ['is_unique' => true]);
        $pool = $this->option($attribute, 'Pool');
        $gym = $this->option($attribute, 'Gym');

        $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-UNIQUE-ONE'), [
            'attributes' => ['amenities' => [$pool->id, $gym->id]],
            'attributes_present' => ['amenities' => '1'],
        ]))->assertRedirect(route('admin.properties.index'));

        $first = Property::query()->where('project_code', 'EAV-UNIQUE-ONE')->firstOrFail()
            ->attributeValues()->firstOrFail();
        $this->assertSame([$pool->id, $gym->id], $first->json_value);

        $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-UNIQUE-TWO'), [
            'attributes' => ['amenities' => [$gym->id, $pool->id]],
            'attributes_present' => ['amenities' => '1'],
        ]))->assertSessionHasErrors('attributes.amenities');

        $this->expectException(QueryException::class);
        PropertyAttributeValue::query()->create([
            'property_id' => $this->property('EAV-UNIQUE-DB')->id,
            'attribute_id' => $attribute->id,
            'json_value' => [$gym->id, $pool->id],
            'unique_hash' => $first->unique_hash,
        ]);
    }

    public function test_numeric_attributes_enforce_decimal_storage_bounds(): void
    {
        $this->attribute('asking_price', AttributeType::Price);

        foreach (['1e3', '123456789012345', '1.1234567'] as $value) {
            $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-NUM-'.md5($value)), [
                'attributes' => ['asking_price' => $value],
            ]))->assertSessionHasErrors('attributes.asking_price');
        }

        $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-NUM-OK'), [
            'attributes' => ['asking_price' => '12345678901234.123456'],
        ]))->assertRedirect(route('admin.properties.index'));
    }

    public function test_scalar_defaults_are_applied_and_optional_radio_can_be_cleared(): void
    {
        $defaulted = $this->attribute('completion_date', AttributeType::Date, [
            'default_value' => ['value' => '2026-07-21'],
        ]);
        $radio = $this->attribute('view_type', AttributeType::Radio);
        $sea = $this->option($radio, 'Sea');
        $property = $this->property('EAV-RADIO-CLEAR');
        $this->value($property, $radio, ['integer_value' => $sea->id]);

        $this->get(route('admin.properties.edit', $property))
            ->assertOk()
            ->assertSee('None');

        $this->put(route('admin.properties.update', $property), array_merge($this->payload('EAV-RADIO-CLEAR'), [
            'attributes' => ['view_type' => ''],
        ]))->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseMissing('property_attribute_values', [
            'property_id' => $property->id,
            'attribute_id' => $radio->id,
        ]);

        $this->post(route('admin.properties.store'), $this->payload('EAV-DEFAULT'))
            ->assertRedirect(route('admin.properties.index'));
        $created = Property::query()->where('project_code', 'EAV-DEFAULT')->firstOrFail();
        $savedDefault = $created->attributeValues()->where('attribute_id', $defaulted->id)->firstOrFail();
        $this->assertSame('2026-07-21', $savedDefault->date_value?->format('Y-m-d'));
    }

    public function test_bulk_delete_commits_before_cleaning_owned_files_and_preserves_library_media(): void
    {
        $property = $this->property('EAV-DELETE');
        $property->update(['thumbnail' => 'properties/thumbnails/delete.jpg']);
        Storage::disk('public')->put('properties/thumbnails/delete.jpg', 'owned');
        Storage::disk('public')->put('media-library/shared.jpg', 'shared');
        Storage::disk('public')->put("properties/attributes/{$property->id}/file/value.pdf", 'owned');

        $this->delete(route('admin.properties.deleteMulti'), ['ids' => [$property->id]])
            ->assertRedirect();

        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
        Storage::disk('public')->assertMissing('properties/thumbnails/delete.jpg');
        Storage::disk('public')->assertMissing("properties/attributes/{$property->id}/file/value.pdf");
        Storage::disk('public')->assertExists('media-library/shared.jpg');
    }

    public function test_later_outer_transaction_failure_rolls_back_dynamic_uploads(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('The deterministic failure trigger is SQLite-specific.');
        }

        $this->attribute('hero_image', AttributeType::Image);
        $similar = $this->property('EAV-SIMILAR');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER fail_property_similar_insert
            BEFORE INSERT ON property_similar_properties
            BEGIN
                SELECT RAISE(ABORT, 'simulated later outer failure');
            END
        SQL);

        $this->withoutExceptionHandling();

        try {
            $this->post(route('admin.properties.store'), array_merge($this->payload('EAV-ROLLBACK'), [
                'similar_property_ids' => [$similar->id],
                'attributes' => [
                    'hero_image' => UploadedFile::fake()->image('rollback.jpg'),
                ],
            ]));
            $this->fail('The simulated later write should fail.');
        } catch (Throwable $exception) {
            $this->assertStringContainsString('simulated later outer failure', $exception->getMessage());
        }

        $this->assertDatabaseMissing('properties', ['project_code' => 'EAV-ROLLBACK']);
        $this->assertSame([], Storage::disk('public')->allFiles('properties/attributes'));
        $this->assertSame([], Storage::disk('public')->allFiles('properties/thumbnails'));
    }

    private function payload(string $projectCode): array
    {
        $group = PropertyAttributeGroup::query()->orderBy('id')->first();
        $thumbnailPath = 'testing/thumbnails/'.Str::slug(strtolower($projectCode)).'.jpg';
        Storage::disk('public')->put($thumbnailPath, $this->tinyJpeg());

        return [
            'thumbnail_media_path' => $thumbnailPath,
            'project_code' => $projectCode,
            'url_key' => Str::slug(strtolower($projectCode)),
            'project_name' => 'Test project',
            'title' => 'Test property',
            'overview' => 'Overview',
            'district_id' => $this->district->id,
            'area_id' => null,
            'property_type_id' => $this->propertyType->id,
            'attribute_group_ids' => array_values(array_filter([(int) ($group?->id ?? 0)])),
            'attribute_group_ids_present' => '1',
            'why_to_buy' => 'Reasons',
            'content' => 'Location details',
            'status' => CmsStatus::PUBLISHED->value,
            'slide_category_ids_present' => '1',
            'slide_category_ids' => [],
            'similar_property_ids' => [],
        ];
    }

    private function tinyJpeg(): string
    {
        $jpeg = hex2bin(
            'ffd8ffe000104a46494600010101006000600000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffdb0043010909090c0b0c180d0d1832211c213232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232ffc00011080001000103011100021101031101ffc40014000100000000000000000000000000000000ffc40014100100000000000000000000000000000000ffda000c0301000210031000003f00bf80ffd9'
        );

        return is_string($jpeg) ? $jpeg : 'jpeg';
    }

    private function attribute(string $code, AttributeType $type, array $overrides = []): PropertyAttribute
    {
        $attribute = PropertyAttribute::factory()->create(array_merge([
            'code' => $code,
            'name' => ['en' => Str::headline($code)],
            'type' => $type,
        ], $overrides));
        $group = PropertyAttributeGroup::query()->firstOrCreate(
            ['position' => 0],
            ['name' => ['en' => 'Property details'], 'is_active' => true]
        );
        $group->attributes()->attach($attribute->id, ['position' => $group->attributes()->count()]);

        return $attribute;
    }

    private function option(PropertyAttribute $attribute, string $label, bool $active = true): PropertyAttributeOption
    {
        return PropertyAttributeOption::factory()->create([
            'attribute_id' => $attribute->id,
            'label' => ['en' => $label],
            'is_active' => $active,
        ]);
    }

    private function value(Property $property, PropertyAttribute $attribute, array $values): PropertyAttributeValue
    {
        return PropertyAttributeValue::factory()->create(array_merge([
            'property_id' => $property->id,
            'attribute_id' => $attribute->id,
        ], $values));
    }

    private function property(string $projectCode): Property
    {
        $property = Property::factory()->create([
            'project_code' => $projectCode,
            'url_key' => Str::slug(strtolower($projectCode)),
            'location_id' => $this->district->id,
            'property_type_id' => $this->propertyType->id,
        ]);

        $groupId = PropertyAttributeGroup::query()->orderBy('id')->value('id');
        if ($groupId !== null) {
            $property->attributeGroups()->sync([(int) $groupId => ['position' => 0]]);
        }

        return $property;
    }

    private function admin(): User
    {
        $permission = Permission::findOrCreate('Property Management', 'web');
        $user = User::query()->create([
            'name' => 'Property Admin',
            'email' => Str::uuid().'@example.test',
            'mobile' => (string) random_int(1000000000, 9999999999),
            'password' => 'password',
            'type' => 'admin',
            'img' => null,
        ]);
        $user->givePermissionTo($permission);

        return $user;
    }
}
