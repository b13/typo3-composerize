<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Third Extension',
    'description' => 'Third Extension for testing composer',
    'category' => 'fe',
    'author' => 'Sample Author',
    'state' => 'stable',
    'clearCacheOnLoad' => false,
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.5.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ]
];
