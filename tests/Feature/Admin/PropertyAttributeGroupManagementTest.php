<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Modules\Core\Contracts\Translation\TranslatorInterface;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;
use Modules\Property\Models\PropertyAttributeValue;
use Modules\Property\Models\PropertyType;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PropertyAttributeGroupManagementTest extends TestCase
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

    public function test_admin_creates_and_updates_a_group_without_losing_other_translations(): void
    {
        app()->setLocale('en');

        $this->post(route('admin.property_attribute_groups.store'), [
            'name' => 'Details',
            'position' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('admin.property_attribute_groups.index'));

        $group = PropertyAttributeGroup::query()->firstOrFail();
        $group->setTranslation('name', 'ar', 'التفاصيل')->save();

        $this->put(route('admin.property_attribute_groups.update', $group), [
            'name' => 'Property details',
            'position' => 5,
            'is_active' => '0',
        ])->assertRedirect(route('admin.property_attribute_groups.index'));

        $group->refresh();
        $this->assertSame('Property details', $group->getTranslation('name', 'en'));
        $this->assertSame('التفاصيل', $group->getTranslation('name', 'ar'));
        $this->assertSame(5, $group->position);
        $this->assertFalse($group->is_active);
    }

    public function test_create_and_checked_update_auto_translate_group_name(): void
    {
        $this->mockTranslator([
            'ar' => [
                'Details' => 'التفاصيل',
                'Property details' => 'تفاصيل العقار',
            ],
            'tr' => [
                'Details' => 'Detaylar',
                'Property details' => 'Mülk detayları',
            ],
        ]);

        app()->setLocale('en');

        $this->post(route('admin.property_attribute_groups.store'), [
            'name' => 'Details',
            'position' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.property_attribute_groups.index'));

        $group = PropertyAttributeGroup::query()->firstOrFail();
        $this->assertSame('التفاصيل', $group->getTranslation('name', 'ar'));
        $this->assertSame('Detaylar', $group->getTranslation('name', 'tr'));

        $this->put(route('admin.property_attribute_groups.update', $group), [
            'name' => 'Property details',
            'position' => 2,
            'is_active' => '1',
            'update_translations' => '1',
        ])->assertRedirect(route('admin.property_attribute_groups.index'));

        $group->refresh();
        $this->assertSame('تفاصيل العقار', $group->getTranslation('name', 'ar'));
        $this->assertSame('Mülk detayları', $group->getTranslation('name', 'tr'));
    }

    public function test_index_exposes_nested_order_and_unassigned_attributes(): void
    {
        $group = PropertyAttributeGroup::factory()->create(['name' => ['en' => 'Location'], 'position' => 0]);
        $first = PropertyAttribute::factory()->create(['code' => 'district']);
        $second = PropertyAttribute::factory()->create(['code' => 'city']);
        $unassigned = PropertyAttribute::factory()->create(['code' => 'reference']);
        $group->attributes()->attach([
            $first->id => ['position' => 1],
            $second->id => ['position' => 0],
        ]);

        $this->get(route('admin.property_attribute_groups.index'))
            ->assertOk()
            ->assertSeeInOrder(['city', 'district'])
            ->assertSee('reference')
            ->assertViewHas('unassignedAttributes', fn ($attributes): bool => $attributes->modelKeys() === [$unassigned->id]);
    }

    public function test_complete_reorder_moves_attributes_between_groups_and_unassigned(): void
    {
        $firstGroup = PropertyAttributeGroup::factory()->create(['position' => 0]);
        $secondGroup = PropertyAttributeGroup::factory()->create(['position' => 1]);
        $first = PropertyAttribute::factory()->create();
        $second = PropertyAttribute::factory()->create();
        $third = PropertyAttribute::factory()->create();
        $firstGroup->attributes()->attach([
            $first->id => ['position' => 0],
            $second->id => ['position' => 1],
        ]);

        $this->post(route('admin.property_attribute_groups.reorder'), [
            'groups' => [
                ['id' => $secondGroup->id, 'attributes' => [$second->id, $third->id]],
                ['id' => $firstGroup->id, 'attributes' => []],
            ],
            'unassigned' => [$first->id],
        ])->assertRedirect(route('admin.property_attribute_groups.index'));

        $this->assertDatabaseHas('property_attribute_groups', ['id' => $secondGroup->id, 'position' => 0]);
        $this->assertDatabaseHas('property_attribute_groups', ['id' => $firstGroup->id, 'position' => 1]);
        $this->assertDatabaseHas('property_attribute_group_mappings', [
            'group_id' => $secondGroup->id,
            'attribute_id' => $second->id,
            'position' => 0,
        ]);
        $this->assertDatabaseHas('property_attribute_group_mappings', [
            'group_id' => $secondGroup->id,
            'attribute_id' => $third->id,
            'position' => 1,
        ]);
        $this->assertDatabaseMissing('property_attribute_group_mappings', ['attribute_id' => $first->id]);
    }

    public function test_reorder_accepts_html_empty_list_encoding(): void
    {
        $group = PropertyAttributeGroup::factory()->create(['position' => 0]);
        $first = PropertyAttribute::factory()->create(['code' => 'facilites']);
        $second = PropertyAttribute::factory()->create(['code' => 'floor_space']);

        // Mimic the browser form: empty group attributes / empty unassigned become ""
        // and Laravel's ConvertEmptyStringsToNull middleware turns them into null.
        $this->post(route('admin.property_attribute_groups.reorder'), [
            'groups' => [
                ['id' => $group->id, 'attributes' => null],
            ],
            'unassigned' => null,
        ])->assertSessionHasErrors(); // missing attributes from the complete set

        $this->post(route('admin.property_attribute_groups.reorder'), [
            'groups' => [
                ['id' => $group->id, 'attributes' => ''],
            ],
            'unassigned' => [$first->id, $second->id],
        ])->assertRedirect(route('admin.property_attribute_groups.index'))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseMissing('property_attribute_group_mappings', ['attribute_id' => $first->id]);
        $this->assertDatabaseMissing('property_attribute_group_mappings', ['attribute_id' => $second->id]);

        $this->post(route('admin.property_attribute_groups.reorder'), [
            'groups' => [
                ['id' => $group->id, 'attributes' => [$first->id, $second->id]],
            ],
            'unassigned' => '',
        ])->assertRedirect(route('admin.property_attribute_groups.index'))
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('property_attribute_group_mappings', [
            'group_id' => $group->id,
            'attribute_id' => $first->id,
            'position' => 0,
        ]);
        $this->assertDatabaseHas('property_attribute_group_mappings', [
            'group_id' => $group->id,
            'attribute_id' => $second->id,
            'position' => 1,
        ]);
    }

    public function test_reorder_rejects_duplicate_missing_and_foreign_ids(): void
    {
        $groups = collect([
            PropertyAttributeGroup::factory()->create(['position' => 0]),
            PropertyAttributeGroup::factory()->create(['position' => 1]),
        ]);
        $attributes = PropertyAttribute::factory()->count(2)->create();

        $payloads = [
            'duplicate group' => [
                'groups' => [
                    ['id' => $groups[0]->id, 'attributes' => [$attributes[0]->id]],
                    ['id' => $groups[0]->id, 'attributes' => []],
                ],
                'unassigned' => [$attributes[1]->id],
            ],
            'duplicate attribute' => [
                'groups' => [
                    ['id' => $groups[0]->id, 'attributes' => [$attributes[0]->id]],
                    ['id' => $groups[1]->id, 'attributes' => []],
                ],
                'unassigned' => [$attributes[0]->id, $attributes[1]->id],
            ],
            'missing group' => [
                'groups' => [
                    ['id' => $groups[0]->id, 'attributes' => [$attributes[0]->id]],
                ],
                'unassigned' => [$attributes[1]->id],
            ],
            'missing attribute' => [
                'groups' => [
                    ['id' => $groups[0]->id, 'attributes' => [$attributes[0]->id]],
                    ['id' => $groups[1]->id, 'attributes' => []],
                ],
                'unassigned' => [],
            ],
            'foreign group' => [
                'groups' => [
                    ['id' => 999999, 'attributes' => [$attributes[0]->id]],
                    ['id' => $groups[1]->id, 'attributes' => []],
                ],
                'unassigned' => [$attributes[1]->id],
            ],
            'foreign attribute' => [
                'groups' => [
                    ['id' => $groups[0]->id, 'attributes' => [999999]],
                    ['id' => $groups[1]->id, 'attributes' => []],
                ],
                'unassigned' => [$attributes[0]->id, $attributes[1]->id],
            ],
        ];

        foreach ($payloads as $payload) {
            $this->from(route('admin.property_attribute_groups.index'))
                ->post(route('admin.property_attribute_groups.reorder'), $payload)
                ->assertRedirect(route('admin.property_attribute_groups.index'))
                ->assertSessionHasErrors();
        }

        $this->assertSame([0, 1], PropertyAttributeGroup::query()->ordered()->pluck('position')->all());
        $this->assertSame(0, DB::table('property_attribute_group_mappings')->count());
    }

    public function test_bulk_delete_detaches_group_without_deleting_attribute_definitions(): void
    {
        $group = PropertyAttributeGroup::factory()->create();
        $attribute = PropertyAttribute::factory()->create();
        $group->attributes()->attach($attribute, ['position' => 0]);
        $value = PropertyAttributeValue::factory()->create([
            'property_id' => $this->property(),
            'attribute_id' => $attribute->id,
            'text_value' => 'Preserved',
        ]);

        $this->delete(route('admin.property_attribute_groups.deleteMulti'), [
            'ids' => [$group->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('property_attribute_groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('property_attribute_group_mappings', ['group_id' => $group->id]);
        $this->assertDatabaseHas('property_attributes', ['id' => $attribute->id]);
        $this->assertDatabaseHas('property_attribute_values', ['id' => $value->id]);
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
