<?php

return [
    'name' => 'Property',

    /** @see Modules/Property/config/bootstrap_icons.php */
    'bootstrap_icons' => require __DIR__.'/bootstrap_icons.php',

    /**
     * Preset labels for property unit types (admin dropdown). Use "Other" for custom names.
     *
     * @var list<string>
     */
    'unit_type_options' => [
        'Studio',
        '1+0',
        '1+1',
        '2+1',
        '3+1',
        '4+1',
        'Duplex',
        'Penthouse',
        'Villa',
        'Other',
    ],
];
