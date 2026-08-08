<?php

namespace Modules\Property\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Base\Models\Media;
use Modules\Property\Database\Factories\PropertyAttributeValueFactory;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Models\PropertyAttributeGroup;
use Modules\Property\Models\PropertyAttributeOption;
use Modules\Property\Models\PropertyAttributeValue;
use Modules\User\Enums\CmsStatus;

/**
 * Demo attribute groups, attributes, options and per-property values so the
 * front-office show page has one filled attribute of every {@see AttributeType}.
 *
 * Safe to re-run: groups match on their English name, attributes on their
 * immutable code, options on attribute + position, values on the
 * (property_id, attribute_id) unique key.
 */
class PropertyAttributeDemoSeeder extends Seeder
{
    private const BROCHURE_PATH = 'properties/attributes/seed/project-brochure.pdf';

    private const VALUE_CHUNK = 500;

    /**
     * @var list<string>
     */
    private array $images = [];

    private ?string $brochure = null;

    public function run(): void
    {
        $this->images = $this->libraryImagePaths();
        $this->brochure = $this->ensureBrochure();

        if ($this->images === []) {
            $this->command?->warn(
                'PropertyAttributeDemoSeeder: no public images found; image and gallery attributes stay empty.'
            );
        }

        ['groups' => $groups, 'attributes' => $attributes] = $this->seedBlueprint();

        [$properties, $values] = $this->bindToPublishedProperties($groups, $attributes);

        $this->command?->info(sprintf(
            'PropertyAttributeDemoSeeder: %d groups, %d attributes, %d options, %d properties bound, %d values written.',
            $groups->count(),
            $attributes->count(),
            PropertyAttributeOption::query()->whereIn('attribute_id', $attributes->pluck('id'))->count(),
            $properties,
            $values,
        ));
    }

    /**
     * Create or refresh every group, attribute and option in the blueprint.
     *
     * @return array{
     *     groups: Collection<int, PropertyAttributeGroup>,
     *     attributes: Collection<int, PropertyAttribute>
     * }
     */
    private function seedBlueprint(): array
    {
        $groups = collect();
        $attributes = collect();

        foreach ($this->blueprint() as $groupPosition => $groupData) {
            $group = $this->upsertGroup($groupData['name'], $groupPosition);
            $groups->push($group);

            foreach ($groupData['attributes'] as $attributePosition => $attributeData) {
                $attribute = $this->upsertAttribute($attributeData);

                DB::table('property_attribute_group_mappings')->updateOrInsert(
                    ['attribute_id' => $attribute->id],
                    ['group_id' => $group->id, 'position' => $attributePosition],
                );

                foreach ($attributeData['options'] ?? [] as $optionPosition => $optionData) {
                    $this->upsertOption($attribute, $optionData, $optionPosition);
                }

                $attributes->push($attribute->load('options'));
            }
        }

        return ['groups' => $groups, 'attributes' => $attributes];
    }

    /**
     * @param  array<string, string>  $name
     */
    private function upsertGroup(array $name, int $position): PropertyAttributeGroup
    {
        $group = PropertyAttributeGroup::query()
            ->where('name->en', $name['en'])
            ->first();

        if ($group === null) {
            return PropertyAttributeGroup::factory()->named($name, $position)->create();
        }

        $group->forceFill([
            'name' => $name,
            'position' => $position,
            'is_active' => true,
        ])->save();

        return $group;
    }

    /**
     * @param  array{code: string, type: AttributeType, name: array<string, string>, help_text?: array<string, string>}  $data
     */
    private function upsertAttribute(array $data): PropertyAttribute
    {
        $attribute = PropertyAttribute::query()->where('code', $data['code'])->first();
        $icon = $this->images === [] ? null : $this->images[crc32($data['code']) % count($this->images)];

        if ($attribute === null) {
            return PropertyAttribute::factory()
                ->withCode($data['code'])
                ->ofType($data['type'])
                ->named($data['name'])
                ->withIcon($icon)
                ->create(['help_text' => $data['help_text'] ?? null]);
        }

        // `code` is immutable on the model, so it is deliberately left out here.
        $attribute->forceFill([
            'name' => $data['name'],
            'help_text' => $data['help_text'] ?? null,
            'image' => $icon,
            'type' => $data['type'],
            'is_active' => true,
        ])->save();

        return $attribute;
    }

    /**
     * @param  array<string, string>  $optionData  locale labels plus optional `icon`
     */
    private function upsertOption(PropertyAttribute $attribute, array $optionData, int $position): void
    {
        $icon = trim((string) ($optionData['icon'] ?? ''));
        unset($optionData['icon']);
        $labels = $optionData;

        $option = PropertyAttributeOption::query()
            ->where('attribute_id', $attribute->id)
            ->where('position', $position)
            ->first();

        if ($option === null) {
            PropertyAttributeOption::factory()
                ->forAttribute($attribute)
                ->labelled($labels, $position)
                ->create([
                    'icon' => $icon !== '' ? $icon : null,
                ]);

            return;
        }

        $option->forceFill([
            'label' => $labels,
            'icon' => $icon !== '' ? $icon : null,
            'is_active' => true,
            'position' => $position,
        ])->save();
    }

    /**
     * Attach every group to each published property and write one value per
     * attribute, leaving a single attribute empty so the "omit empty values"
     * path stays covered.
     *
     * @param  Collection<int, PropertyAttributeGroup>  $groups
     * @param  Collection<int, PropertyAttribute>  $attributes
     * @return array{int, int} properties bound, values written
     */
    private function bindToPublishedProperties(Collection $groups, Collection $attributes): array
    {
        if ($attributes->isEmpty()) {
            return [0, 0];
        }

        $pivot = $groups
            ->values()
            ->mapWithKeys(fn (PropertyAttributeGroup $group, int $index): array => [
                $group->id => ['position' => $index],
            ])
            ->all();

        $propertyCount = 0;
        $valueCount = 0;
        $rows = [];
        $now = Carbon::now();

        Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->select(['id'])
            ->chunkById(100, function (Collection $properties) use (
                $pivot,
                $attributes,
                $now,
                &$rows,
                &$propertyCount,
                &$valueCount,
            ): void {
                foreach ($properties as $property) {
                    $property->attributeGroups()->syncWithoutDetaching($pivot);
                    $propertyCount++;

                    $skipped = $attributes[$property->id % $attributes->count()];
                    PropertyAttributeValue::query()
                        ->where('property_id', $property->id)
                        ->where('attribute_id', $skipped->id)
                        ->delete();

                    foreach ($attributes as $attribute) {
                        if ($attribute->id === $skipped->id) {
                            continue;
                        }

                        $value = $this->demoValue($attribute);
                        if ($value === null || $value === [] || $value === '') {
                            continue;
                        }

                        $rows[] = array_merge(
                            [
                                'property_id' => $property->id,
                                'attribute_id' => $attribute->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ],
                            $this->valueColumns($attribute, $value),
                        );
                        $valueCount++;
                    }
                }

                $rows = $this->flushValues($rows, self::VALUE_CHUNK);
            });

        $this->flushValues($rows, 1);

        return [$propertyCount, $valueCount];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function flushValues(array $rows, int $threshold): array
    {
        if (count($rows) < $threshold || $rows === []) {
            return $rows;
        }

        foreach (array_chunk($rows, self::VALUE_CHUNK) as $chunk) {
            PropertyAttributeValue::query()->upsert(
                $chunk,
                ['property_id', 'attribute_id'],
                [
                    'text_value',
                    'decimal_value',
                    'boolean_value',
                    'integer_value',
                    'date_value',
                    'datetime_value',
                    'json_value',
                    'unique_hash',
                    'updated_at',
                ],
            );
        }

        return [];
    }

    /**
     * `upsert()` bypasses Eloquent casts, so arrays are encoded here.
     *
     * @return array<string, mixed>
     */
    private function valueColumns(PropertyAttribute $attribute, mixed $value): array
    {
        $columns = PropertyAttributeValueFactory::columnsFor($attribute, $value);
        if (is_array($columns['json_value'])) {
            $columns['json_value'] = json_encode($columns['json_value']);
        }

        return $columns;
    }

    private function demoValue(PropertyAttribute $attribute): mixed
    {
        $optionIds = $attribute->options
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        return match ($attribute->type) {
            AttributeType::Text => fake()->randomElement(['24/7', '08:00 - 22:00', '2 shifts daily']),
            AttributeType::Textarea => fake()->paragraph(),
            AttributeType::Number => (string) fake()->randomFloat(1, 1, 60),
            AttributeType::Price => (string) fake()->numberBetween(150, 2500),
            AttributeType::Boolean => fake()->boolean(),
            AttributeType::Radio, AttributeType::Select => $optionIds === []
                ? null
                : fake()->randomElement($optionIds),
            AttributeType::Checkbox, AttributeType::Multiselect => $optionIds === []
                ? []
                : array_values(fake()->randomElements(
                    $optionIds,
                    min(count($optionIds), fake()->numberBetween(3, 6)),
                )),
            AttributeType::Image => $this->images === [] ? null : fake()->randomElement($this->images),
            AttributeType::Gallery => $this->images === []
                ? []
                : array_values(fake()->randomElements($this->images, min(count($this->images), 4))),
            AttributeType::File => $this->brochure,
            AttributeType::Date => Carbon::now()->addDays(fake()->numberBetween(30, 720))->format('Y-m-d'),
            AttributeType::Datetime => Carbon::now()
                ->addDays(fake()->numberBetween(10, 400))
                ->setTime(fake()->numberBetween(8, 18), fake()->randomElement([0, 30]))
                ->format('Y-m-d H:i:s'),
        };
    }

    /**
     * Real public-disk images: media library first (they carry alt text), then
     * any seeded slide images.
     *
     * @return list<string>
     */
    private function libraryImagePaths(): array
    {
        $paths = Media::query()
            ->active()
            ->where('disk', 'public')
            ->where('path', 'like', 'media-library/%')
            ->orderBy('id')
            ->pluck('path')
            ->filter(fn ($path): bool => is_string($path) && Storage::disk('public')->exists($path))
            ->values()
            ->all();

        if ($paths !== []) {
            return $paths;
        }

        return collect(Storage::disk('public')->files('slides'))
            ->filter(fn (string $path): bool => in_array(
                strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                ['jpg', 'jpeg', 'png', 'webp', 'gif'],
                true,
            ))
            ->values()
            ->all();
    }

    /**
     * Minimal one-page PDF so the file attribute resolves to a downloadable asset.
     */
    private function ensureBrochure(): string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists(self::BROCHURE_PATH)) {
            $disk->put(self::BROCHURE_PATH, $this->placeholderPdf());
        }

        return self::BROCHURE_PATH;
    }

    private function placeholderPdf(): string
    {
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] "
                ."/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
        ];

        $stream = 'BT /F1 24 Tf 72 750 Td (IMas demo project brochure) Tj ET';
        $objects[] = "5 0 obj\n<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref'."\n".'0 '.(count($objects) + 1)."\n".'0000000000 65535 f '."\n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf
            .'trailer'."\n".'<< /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n"
            .'startxref'."\n".$xrefPosition."\n".'%%EOF';
    }

    /**
     * @return list<array{name: array<string, string>, attributes: list<array<string, mixed>>}>
     */
    private function blueprint(): array
    {
        return [
            [
                'name' => ['en' => 'Project Overview', 'ar' => 'نظرة عامة على المشروع', 'tr' => 'Proje Genel Bakış'],
                'attributes' => [
                    [
                        'code' => 'project_views',
                        'type' => AttributeType::Select,
                        'name' => ['en' => 'Project Views', 'ar' => 'إطلالات المشروع', 'tr' => 'Proje Manzarası'],
                        'options' => [
                            ['en' => 'Sea view', 'ar' => 'إطلالة بحرية', 'tr' => 'Deniz manzarası', 'icon' => 'bi bi-water'],
                            ['en' => 'City view', 'ar' => 'إطلالة على المدينة', 'tr' => 'Şehir manzarası', 'icon' => 'bi bi-building'],
                            ['en' => 'Forest view', 'ar' => 'إطلالة على الغابة', 'tr' => 'Orman manzarası', 'icon' => 'bi bi-tree'],
                            ['en' => 'Bosphorus view', 'ar' => 'إطلالة على البوسفور', 'tr' => 'Boğaz manzarası', 'icon' => 'bi bi-binoculars'],
                            ['en' => 'Garden view', 'ar' => 'إطلالة على الحديقة', 'tr' => 'Bahçe manzarası', 'icon' => 'bi bi-flower1'],
                            ['en' => 'Mountain view', 'ar' => 'إطلالة جبلية', 'tr' => 'Dağ manzarası', 'icon' => 'bi bi-brightness-high'],
                        ],
                    ],
                    [
                        'code' => 'delivery_date',
                        'type' => AttributeType::Date,
                        'name' => ['en' => 'Delivery Date', 'ar' => 'تاريخ التسليم', 'tr' => 'Teslim Tarihi'],
                    ],
                    [
                        'code' => 'completion_status',
                        'type' => AttributeType::Radio,
                        'name' => ['en' => 'Completion Status', 'ar' => 'حالة الإنجاز', 'tr' => 'Tamamlanma Durumu'],
                        'options' => [
                            ['en' => 'Ready to move', 'ar' => 'جاهز للسكن', 'tr' => 'Taşınmaya hazır', 'icon' => 'bi bi-check-circle'],
                            ['en' => 'Under construction', 'ar' => 'قيد الإنشاء', 'tr' => 'İnşaat halinde', 'icon' => 'bi bi-cone-striped'],
                            ['en' => 'Off-plan', 'ar' => 'على الخارطة', 'tr' => 'Proje aşamasında', 'icon' => 'bi bi-layout-split'],
                            ['en' => 'Delivered', 'ar' => 'تم التسليم', 'tr' => 'Teslim edildi', 'icon' => 'bi bi-patch-check'],
                            ['en' => 'Under renovation', 'ar' => 'قيد التجديد', 'tr' => 'Yenileniyor', 'icon' => 'bi bi-tools'],
                        ],
                    ],
                    [
                        'code' => 'floors_count',
                        'type' => AttributeType::Number,
                        'name' => ['en' => 'Number Of Floors', 'ar' => 'عدد الطوابق', 'tr' => 'Kat Sayısı'],
                    ],
                    [
                        'code' => 'project_brochure',
                        'type' => AttributeType::File,
                        'name' => ['en' => 'Project Brochure', 'ar' => 'كتيب المشروع', 'tr' => 'Proje Broşürü'],
                    ],
                ],
            ],
            [
                'name' => [
                    'en' => 'Location Specifications',
                    'ar' => 'مواصفات الموقع',
                    'tr' => 'Konum Özellikleri',
                ],
                'attributes' => [
                    [
                        'code' => 'distance_to_airport_km',
                        'type' => AttributeType::Number,
                        'name' => [
                            'en' => 'Distance To Airport (km)',
                            'ar' => 'المسافة إلى المطار (كم)',
                            'tr' => 'Havalimanına Uzaklık (km)',
                        ],
                    ],
                    [
                        'code' => 'distance_to_sea_km',
                        'type' => AttributeType::Number,
                        'name' => [
                            'en' => 'Distance To Sea (km)',
                            'ar' => 'المسافة إلى البحر (كم)',
                            'tr' => 'Denize Uzaklık (km)',
                        ],
                    ],
                    [
                        'code' => 'nearby_landmarks',
                        'type' => AttributeType::Multiselect,
                        'name' => ['en' => 'Nearby Landmarks', 'ar' => 'المعالم القريبة', 'tr' => 'Yakın Noktalar'],
                        'options' => [
                            ['en' => 'Shopping mall', 'ar' => 'مركز تسوق', 'tr' => 'Alışveriş merkezi', 'icon' => 'bi bi-cart'],
                            ['en' => 'University', 'ar' => 'جامعة', 'tr' => 'Üniversite', 'icon' => 'bi bi-mortarboard'],
                            ['en' => 'Hospital', 'ar' => 'مستشفى', 'tr' => 'Hastane', 'icon' => 'bi bi-hospital'],
                            ['en' => 'International school', 'ar' => 'مدرسة دولية', 'tr' => 'Uluslararası okul', 'icon' => 'bi bi-journal-text'],
                            ['en' => 'City center', 'ar' => 'مركز المدينة', 'tr' => 'Şehir merkezi', 'icon' => 'bi bi-geo-alt'],
                            ['en' => 'Public park', 'ar' => 'حديقة عامة', 'tr' => 'Halk parkı', 'icon' => 'bi bi-tree-fill'],
                        ],
                    ],
                    [
                        'code' => 'location_note',
                        'type' => AttributeType::Textarea,
                        'name' => ['en' => 'Location Notes', 'ar' => 'ملاحظات الموقع', 'tr' => 'Konum Notları'],
                    ],
                    [
                        'code' => 'neighborhood_map',
                        'type' => AttributeType::Image,
                        'name' => ['en' => 'Neighborhood Map', 'ar' => 'خريطة الحي', 'tr' => 'Mahalle Haritası'],
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Transportation', 'ar' => 'المواصلات', 'tr' => 'Ulaşım'],
                'attributes' => [
                    [
                        'code' => 'transportation_options',
                        'type' => AttributeType::Checkbox,
                        'name' => ['en' => 'Transportation Options', 'ar' => 'وسائل النقل', 'tr' => 'Ulaşım Seçenekleri'],
                        'options' => [
                            ['en' => 'Metro', 'ar' => 'مترو', 'tr' => 'Metro', 'icon' => 'bi bi-train-front'],
                            ['en' => 'Metrobus', 'ar' => 'مترو باص', 'tr' => 'Metrobüs', 'icon' => 'bi bi-truck'],
                            ['en' => 'Tram', 'ar' => 'ترام', 'tr' => 'Tramvay', 'icon' => 'bi bi-train-lightrail-front'],
                            ['en' => 'Bus', 'ar' => 'حافلة', 'tr' => 'Otobüs', 'icon' => 'bi bi-car-front'],
                            ['en' => 'Marmaray', 'ar' => 'مرمراي', 'tr' => 'Marmaray', 'icon' => 'bi bi-train-front-fill'],
                            ['en' => 'Ferry', 'ar' => 'عبّارة', 'tr' => 'Vapur', 'icon' => 'bi bi-water'],
                        ],
                    ],
                    [
                        'code' => 'distance_to_metro_km',
                        'type' => AttributeType::Number,
                        'name' => [
                            'en' => 'Distance To Metro (km)',
                            'ar' => 'المسافة إلى المترو (كم)',
                            'tr' => 'Metroya Uzaklık (km)',
                        ],
                    ],
                    [
                        'code' => 'parking_available',
                        'type' => AttributeType::Boolean,
                        'name' => ['en' => 'Parking Available', 'ar' => 'موقف سيارات متاح', 'tr' => 'Otopark Mevcut'],
                    ],
                    [
                        'code' => 'parking_type',
                        'type' => AttributeType::Select,
                        'name' => ['en' => 'Parking Type', 'ar' => 'نوع الموقف', 'tr' => 'Otopark Tipi'],
                        'options' => [
                            ['en' => 'Closed garage', 'ar' => 'كراج مغلق', 'tr' => 'Kapalı garaj', 'icon' => 'bi bi-p-circle'],
                            ['en' => 'Open parking', 'ar' => 'موقف مكشوف', 'tr' => 'Açık otopark', 'icon' => 'bi bi-p-square-fill'],
                            ['en' => 'Underground', 'ar' => 'تحت الأرض', 'tr' => 'Yeraltı', 'icon' => 'bi bi-layers'],
                            ['en' => 'Private garage', 'ar' => 'كراج خاص', 'tr' => 'Özel garaj', 'icon' => 'bi bi-lock'],
                            ['en' => 'Valet parking', 'ar' => 'خدمة صف السيارات', 'tr' => 'Vale hizmeti', 'icon' => 'bi bi-person-badge'],
                        ],
                    ],
                    [
                        'code' => 'shuttle_service',
                        'type' => AttributeType::Boolean,
                        'name' => ['en' => 'Shuttle Service', 'ar' => 'خدمة النقل المكوكي', 'tr' => 'Servis Aracı'],
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Project Facilities', 'ar' => 'مرافق المشروع', 'tr' => 'Proje Olanakları'],
                'attributes' => [
                    [
                        'code' => 'facilities',
                        'type' => AttributeType::Checkbox,
                        'name' => ['en' => 'Facilities', 'ar' => 'المرافق', 'tr' => 'Olanaklar'],
                        'options' => [
                            ['en' => 'Swimming pool', 'ar' => 'مسبح', 'tr' => 'Yüzme havuzu', 'icon' => 'bi bi-droplet'],
                            ['en' => 'Gym', 'ar' => 'صالة رياضية', 'tr' => 'Spor salonu', 'icon' => 'bi bi-heart'],
                            ['en' => 'Sauna', 'ar' => 'ساونا', 'tr' => 'Sauna', 'icon' => 'bi bi-thermometer-sun'],
                            ['en' => 'Turkish bath', 'ar' => 'حمام تركي', 'tr' => 'Türk hamamı', 'icon' => 'bi bi-moisture'],
                            ['en' => 'Kids playground', 'ar' => 'منطقة ألعاب للأطفال', 'tr' => 'Çocuk oyun alanı', 'icon' => 'bi bi-people'],
                            ['en' => '24/7 security', 'ar' => 'أمن على مدار الساعة', 'tr' => '7/24 güvenlik', 'icon' => 'bi bi-shield-check'],
                            ['en' => 'Green areas', 'ar' => 'مساحات خضراء', 'tr' => 'Yeşil alanlar', 'icon' => 'bi bi-tree-fill'],
                            ['en' => 'Cinema room', 'ar' => 'غرفة سينما', 'tr' => 'Sinema salonu', 'icon' => 'bi bi-film'],
                        ],
                    ],
                    [
                        'code' => 'facilities_gallery',
                        'type' => AttributeType::Gallery,
                        'name' => ['en' => 'Facilities Gallery', 'ar' => 'معرض المرافق', 'tr' => 'Olanak Galerisi'],
                    ],
                    [
                        'code' => 'maintenance_fee',
                        'type' => AttributeType::Price,
                        'name' => [
                            'en' => 'Monthly Maintenance Fee',
                            'ar' => 'رسوم الصيانة الشهرية',
                            'tr' => 'Aylık Aidat',
                        ],
                    ],
                    [
                        'code' => 'security_hours',
                        'type' => AttributeType::Text,
                        'name' => ['en' => 'Security Hours', 'ar' => 'ساعات الأمن', 'tr' => 'Güvenlik Saatleri'],
                    ],
                    [
                        'code' => 'facilities_note',
                        'type' => AttributeType::Textarea,
                        'name' => ['en' => 'Facilities Notes', 'ar' => 'ملاحظات المرافق', 'tr' => 'Olanak Notları'],
                    ],
                ],
            ],
            [
                'name' => ['en' => 'Apartment Features', 'ar' => 'مميزات الشقة', 'tr' => 'Daire Özellikleri'],
                'attributes' => [
                    [
                        'code' => 'electrical_devices_in_the_apartment',
                        'type' => AttributeType::Checkbox,
                        'name' => [
                            'en' => 'Electrical Devices In The Apartment',
                            'ar' => 'الأجهزة الكهربائية في الشقة',
                            'tr' => 'Dairedeki Elektrikli Cihazlar',
                        ],
                        'options' => [
                            ['en' => 'Smart system', 'ar' => 'نظام ذكي', 'tr' => 'Akıllı sistem', 'icon' => 'bi bi-wifi'],
                            ['en' => 'Heating system', 'ar' => 'نظام تدفئة', 'tr' => 'Isıtma sistemi', 'icon' => 'bi bi-thermometer-half'],
                            ['en' => 'Air conditioning', 'ar' => 'تكييف هواء', 'tr' => 'Klima', 'icon' => 'bi bi-fan'],
                            ['en' => 'Stove', 'ar' => 'موقد', 'tr' => 'Ocak', 'icon' => 'bi bi-fire'],
                            ['en' => 'Oven', 'ar' => 'فرن', 'tr' => 'Fırın', 'icon' => 'bi bi-egg-fried'],
                            ['en' => 'Aspirator', 'ar' => 'شفاط', 'tr' => 'Aspiratör', 'icon' => 'bi bi-wind'],
                            ['en' => 'Microwave', 'ar' => 'ميكروويف', 'tr' => 'Mikrodalga', 'icon' => 'bi bi-lightning'],
                            ['en' => 'Dishwasher', 'ar' => 'غسالة صحون', 'tr' => 'Bulaşık makinesi', 'icon' => 'bi bi-droplet-fill'],
                            ['en' => 'Washing machine', 'ar' => 'غسالة ملابس', 'tr' => 'Çamaşır makinesi', 'icon' => 'bi bi-arrow-left-right'],
                            ['en' => 'Refrigerator', 'ar' => 'ثلاجة', 'tr' => 'Buzdolabı', 'icon' => 'bi bi-snow'],
                        ],
                    ],
                    [
                        'code' => 'smart_home_system',
                        'type' => AttributeType::Boolean,
                        'name' => ['en' => 'Smart Home System', 'ar' => 'نظام المنزل الذكي', 'tr' => 'Akıllı Ev Sistemi'],
                    ],
                    [
                        'code' => 'flooring_type',
                        'type' => AttributeType::Select,
                        'name' => ['en' => 'Flooring Type', 'ar' => 'نوع الأرضيات', 'tr' => 'Zemin Tipi'],
                        'options' => [
                            ['en' => 'Ceramic', 'ar' => 'سيراميك', 'tr' => 'Seramik', 'icon' => 'bi bi-grid-3x3-gap'],
                            ['en' => 'Parquet', 'ar' => 'باركيه', 'tr' => 'Parke', 'icon' => 'bi bi-border-all'],
                            ['en' => 'Marble', 'ar' => 'رخام', 'tr' => 'Mermer', 'icon' => 'bi bi-gem'],
                            ['en' => 'Laminate', 'ar' => 'لامينيت', 'tr' => 'Laminat', 'icon' => 'bi bi-layers'],
                            ['en' => 'Porcelain', 'ar' => 'بورسلان', 'tr' => 'Porselen', 'icon' => 'bi bi-square'],
                        ],
                    ],
                    [
                        'code' => 'handover_datetime',
                        'type' => AttributeType::Datetime,
                        'name' => [
                            'en' => 'Handover Appointment',
                            'ar' => 'موعد التسليم',
                            'tr' => 'Teslim Randevusu',
                        ],
                    ],
                    [
                        'code' => 'kitchen_style',
                        'type' => AttributeType::Radio,
                        'name' => ['en' => 'Kitchen Style', 'ar' => 'نمط المطبخ', 'tr' => 'Mutfak Tarzı'],
                        'options' => [
                            ['en' => 'Open kitchen', 'ar' => 'مطبخ مفتوح', 'tr' => 'Açık mutfak', 'icon' => 'bi bi-door-open'],
                            ['en' => 'Closed kitchen', 'ar' => 'مطبخ مغلق', 'tr' => 'Kapalı mutfak', 'icon' => 'bi bi-door-closed'],
                            ['en' => 'American kitchen', 'ar' => 'مطبخ أمريكي', 'tr' => 'Amerikan mutfak', 'icon' => 'bi bi-layout-three-columns'],
                            ['en' => 'Island kitchen', 'ar' => 'مطبخ بجزيرة', 'tr' => 'Adalı mutfak', 'icon' => 'bi bi-bounding-box'],
                            ['en' => 'Galley kitchen', 'ar' => 'مطبخ ممر', 'tr' => 'Koridor mutfak', 'icon' => 'bi bi-layout-split'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
