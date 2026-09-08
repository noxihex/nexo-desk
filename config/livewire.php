<?php

return [
    'component_layout' => 'components.layouts.modern',

    'make_command' => [
        'type' => 'class',
        'emoji' => false,
        'with' => [
            'js' => false,
            'css' => false,
            'test' => false,
        ],
    ],

    // Assets are explicit in the modern layout so legacy pages remain untouched.
    'inject_assets' => false,

    'pagination_theme' => 'tailwind',
];
