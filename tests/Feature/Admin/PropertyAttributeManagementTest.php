<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Modules\Base\Models\Media;
use Modules\Core\Contracts\Translation\TranslatorInterface;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeValue;
use Modules\Property\Models\PropertyType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PropertyAttributeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->admin());
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleCookieRedirect::class,
            LocaleSessionRedirect::class,
        ]);
    }

    public function test_admin_creates_a_translated_attribute_with_ordered_options(): void
    {
        $this->post(route('admin.property_attributes.store'), [
            'code' => 'heating_type',
            'name' => 'Heating type',
            'help_text' => 'Choose every available system.',
            'type' => AttributeType::Checkbox->value,
            'is_required' => '1',
            'is_active' => '1',
            'options' => [
                ['label' => 'Gas', 'icon' => 'bi bi-fire', 'is_active' => '1'],
                ['label' => 'Electric', 'icon' => 'bi bi-lightning', 'is_active' => '1'],
            ],
        ])->assertRedirect(route('admin.property_attributes.index'));

        $attribute = PropertyAttribute::query()->where('code', 'heating_type')->firstOrFail();

        $this->assertSame('Heating type', $attribute->getTranslation('name', app()->getLocale()));
        $this->assertSame('Choose every available system.', $attribute->getTranslation('help_text', app()->getLocale()));
        $this->assertSame(['Gas', 'Electric'], $attribute->options->map(
            fn ($option) => $option->getTranslation('label', app()->getLocale())
        )->all());
        $this->assertSame(['bi bi-fire', 'bi bi-lightning'], $attribute->options->pluck('icon')->all());
        $this->assertSame([0, 1], $attribute->options->pluck('position')->all());
    }

    public function test_admin_attribute_screens_render(): void
    {
        $attribute = PropertyAttribute::factory()->create();

        $this->get(route('admin.property_attributes.index'))->assertOk();
        $this->get(route('admin.property_attributes.create'))->assertOk();
        $this->get(route('admin.property_attributes.edit', $attribute))->assertOk();
    }

    public function test_update_preserves_code_and_other_locale_translations(): void
    {
        $attribute = PropertyAttribute::factory()->create([
            'code' => 'bedrooms',
            'name' => ['en' => 'Bedrooms', 'ar' => 'غرف النوم'],
            'help_text' => ['en' => 'Room count', 'ar' => 'عدد الغرف'],
            'type' => AttributeType::Number,
        ]);

        app()->setLocale('en');

        $this->put(route('admin.property_attributes.update', $attribute), [
            'code' => 'attempted_change',
            'name' => 'Bedroom count',
            'help_text' => 'Number of bedrooms',
            'type' => AttributeType::Number->value,
            'is_active' => '1',
        ])->assertRedirect(route('admin.property_attributes.index'));

        $attribute->refresh();
        $this->assertSame('bedrooms', $attribute->code);
        $this->assertSame('Bedroom count', $attribute->getTranslation('name', 'en'));
        $this->assertSame('غرف النوم', $attribute->getTranslation('name', 'ar'));
    }

    public function test_create_and_checked_update_auto_translate_name_help_and_options(): void
    {
        $this->mockTranslator([
            'ar' => [
                'Heating type' => 'نوع التدفئة',
                'Choose every available system.' => 'اختر كل نظام متاح.',
                'Gas' => 'غاز',
                'Electric' => 'كهرباء',
                'Heating systems' => 'أنظمة التدفئة',
                'Select available systems.' => 'حدد الأنظمة المتاحة.',
                'Natural gas' => 'غاز طبيعي',
                'Electric heat' => 'تدفئة كهربائية',
            ],
            'tr' => [
                'Heating type' => 'Isıtma tipi',
                'Choose every available system.' => 'Mevcut her sistemi seçin.',
                'Gas' => 'Gaz',
                'Electric' => 'Elektrik',
                'Heating systems' => 'Isıtma sistemleri',
                'Select available systems.' => 'Mevcut sistemleri seçin.',
                'Natural gas' => 'Doğalgaz',
                'Electric heat' => 'Elektrikli ısıtma',
            ],
        ]);

        app()->setLocale('en');

        $this->post(route('admin.property_attributes.store'), [
            'code' => 'heating_type',
            'name' => 'Heating type',
            'help_text' => 'Choose every available system.',
            'type' => AttributeType::Checkbox->value,
            'is_required' => '1',
            'is_active' => '1',
            'options' => [
                ['label' => 'Gas', 'is_active' => '1'],
                ['label' => 'Electric', 'is_active' => '1'],
            ],
        ])->assertRedirect(route('admin.property_attributes.index'));

        $attribute = PropertyAttribute::query()->where('code', 'heating_type')->firstOrFail();
        $this->assertSame('نوع التدفئة', $attribute->getTranslation('name', 'ar'));
        $this->assertSame('Isıtma tipi', $attribute->getTranslation('name', 'tr'));
        $this->assertSame('اختر كل نظام متاح.', $attribute->getTranslation('help_text', 'ar'));
        $this->assertSame(['غاز', 'كهرباء'], $attribute->options->map(
            fn ($option) => $option->getTranslation('label', 'ar')
        )->all());

        $this->put(route('admin.property_attributes.update', $attribute), [
            'name' => 'Heating systems',
            'help_text' => 'Select available systems.',
            'type' => AttributeType::Checkbox->value,
            'is_active' => '1',
            'update_translations' => '1',
            'options' => [
                ['id' => $attribute->options[0]->id, 'label' => 'Natural gas', 'is_active' => '1'],
                ['id' => $attribute->options[1]->id, 'label' => 'Electric heat', 'is_active' => '1'],
            ],
        ])->assertRedirect(route('admin.property_attributes.index'));

        $attribute->refresh()->load('options');
        $this->assertSame('أنظمة التدفئة', $attribute->getTranslation('name', 'ar'));
        $this->assertSame('Isıtma sistemleri', $attribute->getTranslation('name', 'tr'));
        $this->assertSame(['غاز طبيعي', 'تدفئة كهربائية'], $attribute->options->map(
            fn ($option) => $option->getTranslation('label', 'ar')
        )->all());
    }

    public function test_in_use_attribute_and_option_cannot_be_deleted(): void
    {
        $attribute = PropertyAttribute::factory()->create(['type' => AttributeType::Select]);
        $option = $attribute->options()->create([
            'label' => ['en' => 'Pool'],
            'position' => 0,
            'is_active' => true,
        ]);
        PropertyAttributeValue::factory()->create([
            'property_id' => $this->property(),
            'attribute_id' => $attribute->id,
            'integer_value' => $option->id,
        ]);

        $this->delete(route('admin.property_attributes.deleteMulti'), [
            'ids' => [$attribute->id],
        ])->assertSessionHasErrors('ids');

        $this->put(route('admin.property_attributes.update', $attribute), [
            'name' => 'Updated',
            'type' => AttributeType::Select->value,
            'is_active' => '1',
            'options' => [],
        ])->assertSessionHasErrors('options');

        $this->assertDatabaseHas('property_attributes', ['id' => $attribute->id]);
        $this->assertDatabaseHas('property_attribute_options', ['id' => $option->id]);

        $checkbox = PropertyAttribute::factory()->create(['type' => AttributeType::Checkbox]);
        $usedCheckboxOption = $checkbox->options()->create([
            'label' => ['en' => 'Balcony'],
            'position' => 0,
            'is_active' => true,
        ]);
        $keptCheckboxOption = $checkbox->options()->create([
            'label' => ['en' => 'Terrace'],
            'position' => 1,
            'is_active' => true,
        ]);
        PropertyAttributeValue::factory()->create([
            'property_id' => $this->property(),
            'attribute_id' => $checkbox->id,
            'json_value' => [$usedCheckboxOption->id],
        ]);

        $this->put(route('admin.property_attributes.update', $checkbox), [
            'name' => 'Outdoor spaces',
            'type' => AttributeType::Checkbox->value,
            'is_active' => '1',
            'options' => [
                ['id' => $keptCheckboxOption->id, 'label' => 'Terrace', 'is_active' => '1'],
            ],
        ])->assertSessionHasErrors('options');

        $this->assertDatabaseHas('property_attribute_options', ['id' => $usedCheckboxOption->id]);
    }

    public function test_invalid_option_payload_and_type_change_with_values_are_rejected(): void
    {
        $this->post(route('admin.property_attributes.store'), [
            'code' => 'invalid_select',
            'name' => 'Invalid select',
            'type' => AttributeType::Select->value,
            'is_active' => '1',
            'options' => [],
        ])->assertSessionHasErrors('options');

        $attribute = PropertyAttribute::factory()->create(['type' => AttributeType::Text]);
        PropertyAttributeValue::factory()->create([
            'property_id' => $this->property(),
            'attribute_id' => $attribute->id,
            'text_value' => 'Used',
        ]);

        $this->put(route('admin.property_attributes.update', $attribute), [
            'name' => 'Used attribute',
            'type' => AttributeType::Number->value,
            'is_active' => '1',
        ])->assertSessionHasErrors('type');
    }

    public function test_unique_and_validation_metadata_are_restricted_to_compatible_definitions(): void
    {
        $this->post(route('admin.property_attributes.store'), [
            'code' => 'unique_image',
            'name' => 'Unique image',
            'type' => AttributeType::Image->value,
            'is_unique' => '1',
            'is_active' => '1',
        ])->assertSessionHasErrors('is_unique');

        $this->post(route('admin.property_attributes.store'), [
            'code' => 'invalid_text_rule',
            'name' => 'Invalid text rule',
            'type' => AttributeType::Number->value,
            'validation' => 'email',
            'regex' => '/foo/',
            'is_active' => '1',
        ])->assertSessionHasErrors(['validation', 'regex']);

        $this->post(route('admin.property_attributes.store'), [
            'code' => 'invalid_numeric_rule',
            'name' => 'Invalid numeric rule',
            'type' => AttributeType::Text->value,
            'validation' => 'numeric',
            'is_active' => '1',
        ])->assertSessionHasErrors('validation');

        $attribute = PropertyAttribute::factory()->create([
            'type' => AttributeType::Text,
            'is_unique' => false,
        ]);
        PropertyAttributeValue::factory()->create([
            'property_id' => $this->property(),
            'attribute_id' => $attribute->id,
            'text_value' => 'existing',
        ]);

        $this->put(route('admin.property_attributes.update', $attribute), [
            'name' => 'Existing attribute',
            'type' => AttributeType::Text->value,
            'is_unique' => '1',
            'is_active' => '1',
        ])->assertSessionHasErrors('is_unique');
    }

    public function test_defaults_are_type_aware_and_rejected_for_option_and_media_types(): void
    {
        foreach ([
            [AttributeType::Number, '1e3'],
            [AttributeType::Date, '21/07/2026'],
            [AttributeType::Boolean, 'yes'],
            [AttributeType::File, 'document.pdf'],
        ] as $index => [$type, $default]) {
            $this->post(route('admin.property_attributes.store'), [
                'code' => "invalid_default_{$index}",
                'name' => 'Invalid default',
                'type' => $type->value,
                'default_value' => $default,
                'is_active' => '1',
            ])->assertSessionHasErrors('default_value');
        }

        $this->post(route('admin.property_attributes.store'), [
            'code' => 'invalid_option_default',
            'name' => 'Invalid option default',
            'type' => AttributeType::Select->value,
            'default_value' => '1',
            'is_active' => '1',
            'options' => [
                ['id' => 7, 'label' => 'One', 'is_active' => '1'],
                ['id' => 7, 'label' => 'Two', 'is_active' => '1'],
            ],
        ])->assertSessionHasErrors(['default_value', 'options.1.id']);

        $this->post(route('admin.property_attributes.store'), [
            'code' => 'valid_datetime_default',
            'name' => 'Valid datetime default',
            'type' => AttributeType::Datetime->value,
            'default_value' => '2026-07-21T19:30',
            'is_active' => '1',
        ])->assertRedirect(route('admin.property_attributes.index'));

        $this->assertDatabaseHas('property_attributes', ['code' => 'valid_datetime_default']);
    }

    public function test_admin_can_attach_and_remove_attribute_image(): void
    {
        Media::query()->create([
            'name' => 'Attribute icon',
            'path' => 'media-library/attribute-icon.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 36,
            'width' => 36,
            'height' => 36,
        ]);

        $this->post(route('admin.property_attributes.store'), [
            'code' => 'parking',
            'name' => 'Parking',
            'type' => AttributeType::Boolean->value,
            'is_active' => '1',
            'img_media_path' => 'media-library/attribute-icon.jpg',
        ])->assertRedirect(route('admin.property_attributes.index'));

        $attribute = PropertyAttribute::query()->where('code', 'parking')->firstOrFail();
        $this->assertSame('media-library/attribute-icon.jpg', $attribute->image);
        $this->assertStringContainsString('media-library/attribute-icon.jpg', (string) $attribute->image_link);

        $this->get(route('admin.property_attributes.index'))
            ->assertOk()
            ->assertSee('media-library/attribute-icon.jpg', false);

        $this->get(route('admin.property_attributes.edit', $attribute))
            ->assertOk()
            ->assertSee('width: 36px', false)
            ->assertSee('media-library/attribute-icon.jpg', false);

        $this->put(route('admin.property_attributes.update', $attribute), [
            'name' => 'Parking',
            'type' => AttributeType::Boolean->value,
            'is_active' => '1',
            'img_remove' => '1',
            'img_media_path' => 'null',
        ])->assertRedirect(route('admin.property_attributes.index'));

        $attribute->refresh();
        $this->assertNull($attribute->image);
        $this->assertNull($attribute->image_link);
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

    /**
     * @param  array<string, array<string, string>>  $map
     */
    private function mockTranslator(array $map): void
    {
        $translator = \Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('otherLanguages')->andReturn(array_keys($map));
        $translator->shouldReceive('translate')
            ->andReturnUsing(function (string $language, string $content) use ($map): string {
                return $map[$language][$content] ?? "{$language}:{$content}";
            });

        $this->app->instance(TranslatorInterface::class, $translator);
    }

    private function property(): Property
    {
        Location::query()->create([
            'name' => ['en' => 'Test area'],
            'type' => LocationType::Area,
        ]);
        PropertyType::factory()->create();

        return Property::factory()->create();
    }
}
