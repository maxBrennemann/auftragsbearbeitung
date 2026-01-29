<?php

/**
 * This array defines global configs that are used all over the project.
 * The settings section defines all the user configs that can be stored via the Settings and ClientSettings classes.
 */
return [
    'paths' => [
        'uploadDir' => [
            'default'   => ROOT . 'storage/upload/',
        ],
        'generatedDir'  => ROOT . 'storage/generated/',
        'cacheDir'      => ROOT . 'storage/cache/',
        'logDir'        => ROOT . 'storage/logs/',
    ],
    'appName' => 'Auftragsbearbeitung',
    'lang' => 'DE-de',
    'locale' => 'de',

    'settings' => [
        'errorReporting' => [
            'scope' => 'global',
            'type' => 'bool',
            'default' => false,
        ],
        'cacheStatus' => [
            'scope' => 'global',
            'type' => 'bool',
            'default' => false,
        ],
        'showTimeTracking' => [
            'scope' => 'user',
            'type' => 'bool',
            'default' => false,
        ],
        'invoice.defaultWage' => [
            'scope' => 'global',
            'type' => 'number',
            'default' => 0,
        ],
        'invoice.filterOrderItems' => [
            'scope' => 'user',
            'type' => 'bool',
            'default' => true,
        ],
        'invoice.dueDate' => [
            'scope' => 'global',
            'type' => 'number',
            'default' => 0,
        ],
        'company.name' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => 'Auftragsbearbeitung',
        ],
        'company.address' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.zip' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.city' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.phone' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.email' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.website' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.country' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.imprint' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.bank' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.IBAN' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.UstIdNr' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
        'company.logoId' => [
            'scope' => 'global',
            'type' => 'number',
            'default' => 0,
        ],
        'company.BIC' => [
            'scope' => 'global',
            'type' => 'string',
            'default' => '',
        ],
    ],
];
