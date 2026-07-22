<?php

namespace Tests\Unit;

use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PropertyEavNavigationTranslationsTest extends TestCase
{
    public function test_property_settings_navigation_contains_eav_links_and_active_keys(): void
    {
        $navigation = file_get_contents($this->projectPath('resources/views/components/admin/side-nav.blade.php'));

        $this->assertIsString($navigation);
        $this->assertStringContainsString("{{ __('Property Settings') }}", $navigation);
        $this->assertStringContainsString("route('admin.property_attributes.index')", $navigation);
        $this->assertStringContainsString("{{ __('Attributes') }}", $navigation);
        $this->assertStringContainsString("route('admin.property_attribute_groups.index')", $navigation);
        $this->assertStringContainsString("{{ __('Attribute Groups') }}", $navigation);
        $this->assertStringContainsString("isset(\$active['property_attributes'])", $navigation);
        $this->assertStringContainsString("isset(\$active['property_attribute_groups'])", $navigation);
        $this->assertGreaterThanOrEqual(2, substr_count($navigation, '$propertySettingsActive'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function localeProvider(): array
    {
        return [
            'English' => ['en'],
            'Arabic' => ['ar'],
            'Turkish' => ['tr'],
        ];
    }

    /**
     * @throws JsonException
     */
    #[DataProvider('localeProvider')]
    public function test_property_eav_translation_files_are_valid_and_complete(string $locale): void
    {
        $contents = file_get_contents($this->projectPath("Modules/Property/lang/{$locale}.json"));

        $this->assertIsString($contents);
        $translations = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($translations);

        foreach ($this->requiredTranslationKeys() as $key) {
            $this->assertArrayHasKey($key, $translations, "{$locale}.json is missing [{$key}].");
            $this->assertIsString($translations[$key]);
            $this->assertNotSame('', trim($translations[$key]), "{$locale}.json has an empty [{$key}] translation.");

            preg_match_all('/:[A-Za-z_]+/', $key, $keyPlaceholders);
            preg_match_all('/:[A-Za-z_]+/', $translations[$key], $valuePlaceholders);
            $this->assertSame(
                $keyPlaceholders[0],
                $valuePlaceholders[0],
                "{$locale}.json must preserve placeholders for [{$key}]."
            );
        }
    }

    /**
     * @return list<string>
     */
    private function requiredTranslationKeys(): array
    {
        return [
            'Property Settings',
            'Attributes',
            'Attribute Groups',
            'Properties',
            'Property attributes',
            'Add property attribute',
            'Edit property attribute',
            'Add attribute',
            'Search in property attributes',
            'Attribute definition',
            'Permanent machine name. Lowercase letters, numbers, and underscores.',
            'Name',
            'Code',
            'Help text',
            'Type',
            'Validation rule',
            'None',
            'Regular expression',
            'Default value',
            'Options',
            'Add option',
            'Option label',
            'Move option up',
            'Move option down',
            'Remove option',
            'Settings',
            'Required',
            'Unique',
            'Active',
            'Inactive',
            'Discard',
            'Save Changes',
            'Text',
            'Textarea',
            'Number',
            'Price',
            'Boolean',
            'Checkbox',
            'Radio',
            'Select',
            'Multiselect',
            'Image',
            'Gallery',
            'File',
            'Date',
            'Datetime',
            'Email',
            'Url',
            'Integer',
            'Numeric',
            'Alpha',
            'Alpha num',
            'Attribute groups',
            'Add attribute group',
            'Edit attribute group',
            'Attribute group',
            'Position',
            'Delete selected',
            'Add group',
            'Select group',
            'Move group up',
            'Move group down',
            'Unassigned attributes',
            'Save ordering',
            'Move attribute to group',
            'Unassigned',
            'Move attribute up',
            'Move attribute down',
            'Status',
            'Edit',
            'Yes',
            'Remove',
            'Choose from library',
            'Gallery item controls',
            'Move up',
            'Move down',
            'Open current file',
            'At least one option is required for this attribute type.',
            'Options are not allowed for this attribute type.',
            'The regular expression is invalid.',
            'Every attribute group must be represented exactly once.',
            'Every property attribute must be represented exactly once.',
            'The type cannot be changed after values have been saved.',
            'An attribute with saved values cannot be deleted.',
            'One or more submitted options are invalid.',
            'An option with saved values cannot be removed.',
            'The :attribute field is required.',
            'The :attribute has already been taken.',
            'The retained media selection is invalid.',
            'The :attribute may not have more than :max items.',
            'The selected :attribute is invalid.',
            'The :attribute format is invalid.',
            'The :attribute field must be a valid date and time.',
        ];
    }

    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 2).'/'.$path;
    }
}
