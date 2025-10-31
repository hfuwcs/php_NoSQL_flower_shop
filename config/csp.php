<?php

return [
    'presets' => [
        Spatie\Csp\Presets\Basic::class,
    ],

    /*
     * This policy which will be applied to all responses.
     */
    'enabled' => env('CSP_ENABLED', true),

    /*
     * The class responsible for generating the CSP header.
     */
    'report_uri' => env('CSP_REPORT_URI', false),

    /*
     * If enabled, the CSP header will be added to all responses.
     */
    'report_only' => env('CSP_REPORT_ONLY', false),

    /*
     * All directives will be forgotten and only the directives specified here
     * will be used.
     */
    'directives' => [
        'base-uri' => [
            'self',
        ],
        'connect-src' => [
            'self',
            'api.stripe.com',
        ],
        'default-src' => [
            'self',
        ],
        'form-action' => [
            'self',
        ],
        'img-src' => [
            'self',
            'data:',
            'via.placeholder.com',
        ],
        'media-src' => [
            'self',
        ],
        'object-src' => [
            'none',
        ],
        'script-src' => [
            'self',
            'js.stripe.com',
            'm.stripe.network',
        ],
        'style-src' => [
            'self',
            'fonts.bunny.net',
            "'unsafe-inline'",
        ],
        'font-src' => [
            'self',
            'fonts.bunny.net',
        ],
        'frame-src' => [
            'self',
            'js.stripe.com',
        ],
    ],

    /*
     * The given nonce will be configured on the response and can be used to
     * enable inline scripts and styles.
     */
    'add_nonce_to' => [
        'script-src',
        'style-src',
    ],
];