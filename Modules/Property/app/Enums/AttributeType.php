<?php

namespace Modules\Property\Enums;

/**
 * Input types for catalog attributes (Bagisto-style).
 */
enum AttributeType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Boolean = 'boolean';
    case Select = 'select';
    case Multiselect = 'multiselect';
    case Price = 'price';
    case Numeric = 'numeric';
    case Date = 'date';
    case DateTime = 'datetime';
    case Image = 'image';
    case File = 'file';
    case Color = 'color';
}
