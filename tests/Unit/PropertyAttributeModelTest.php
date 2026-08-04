<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\PropertyAttribute;
use Tests\TestCase;

class PropertyAttributeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_code_cannot_be_changed_after_creation(): void
    {
        $attribute = PropertyAttribute::factory()->create([
            'code' => 'bedrooms',
            'type' => AttributeType::Number,
        ]);

        $attribute->code = 'bedroom_count';

        $this->expectException(LogicException::class);
        $attribute->save();
    }

    public function test_image_link_returns_storage_url_when_set(): void
    {
        $attribute = PropertyAttribute::factory()->create([
            'image' => 'media-library/icon.png',
        ]);

        $this->assertStringContainsString('media-library/icon.png', (string) $attribute->image_link);

        $attribute->image = null;
        $this->assertNull($attribute->image_link);
    }
}
