<?php

namespace Modules\Property\Enums;

enum AttributeType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Price = 'price';
    case Boolean = 'boolean';
    case Checkbox = 'checkbox';
    case Radio = 'radio';
    case Select = 'select';
    case Multiselect = 'multiselect';
    case Image = 'image';
    case Gallery = 'gallery';
    case File = 'file';
    case Date = 'date';
    case Datetime = 'datetime';

    public function hasOptions(): bool
    {
        return match ($this) {
            self::Checkbox, self::Radio, self::Select, self::Multiselect => true,
            default => false,
        };
    }

    public function isMultiple(): bool
    {
        return match ($this) {
            self::Checkbox, self::Multiselect, self::Gallery => true,
            default => false,
        };
    }

    public function isMedia(): bool
    {
        return match ($this) {
            self::Image, self::Gallery, self::File => true,
            default => false,
        };
    }

    public function valueColumn(): string
    {
        return match ($this) {
            self::Text, self::Textarea, self::Image, self::File => 'text_value',
            self::Number, self::Price => 'decimal_value',
            self::Boolean => 'boolean_value',
            self::Radio, self::Select => 'integer_value',
            self::Checkbox, self::Multiselect, self::Gallery => 'json_value',
            self::Date => 'date_value',
            self::Datetime => 'datetime_value',
        };
    }
}
