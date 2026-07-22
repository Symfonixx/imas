<?php

namespace Tests\Unit;

use Modules\Property\Enums\AttributeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttributeTypeTest extends TestCase
{
    /**
     * @return array<string, array{AttributeType, string}>
     */
    public static function valueColumnProvider(): array
    {
        return [
            'text' => [AttributeType::Text, 'text_value'],
            'textarea' => [AttributeType::Textarea, 'text_value'],
            'number' => [AttributeType::Number, 'decimal_value'],
            'price' => [AttributeType::Price, 'decimal_value'],
            'boolean' => [AttributeType::Boolean, 'boolean_value'],
            'checkbox' => [AttributeType::Checkbox, 'json_value'],
            'radio' => [AttributeType::Radio, 'integer_value'],
            'select' => [AttributeType::Select, 'integer_value'],
            'multiselect' => [AttributeType::Multiselect, 'json_value'],
            'image' => [AttributeType::Image, 'text_value'],
            'gallery' => [AttributeType::Gallery, 'json_value'],
            'file' => [AttributeType::File, 'text_value'],
            'date' => [AttributeType::Date, 'date_value'],
            'datetime' => [AttributeType::Datetime, 'datetime_value'],
        ];
    }

    #[DataProvider('valueColumnProvider')]
    public function test_it_maps_each_type_to_its_storage_column(AttributeType $type, string $column): void
    {
        $this->assertSame($column, $type->valueColumn());
    }

    public function test_it_reports_type_capabilities(): void
    {
        $this->assertTrue(AttributeType::Select->hasOptions());
        $this->assertTrue(AttributeType::Radio->hasOptions());
        $this->assertTrue(AttributeType::Checkbox->hasOptions());
        $this->assertTrue(AttributeType::Multiselect->hasOptions());
        $this->assertFalse(AttributeType::Text->hasOptions());

        $this->assertTrue(AttributeType::Checkbox->isMultiple());
        $this->assertTrue(AttributeType::Multiselect->isMultiple());
        $this->assertTrue(AttributeType::Gallery->isMultiple());
        $this->assertFalse(AttributeType::Select->isMultiple());

        $this->assertTrue(AttributeType::Image->isMedia());
        $this->assertTrue(AttributeType::Gallery->isMedia());
        $this->assertTrue(AttributeType::File->isMedia());
        $this->assertFalse(AttributeType::Textarea->isMedia());
    }
}
