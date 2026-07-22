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
}
