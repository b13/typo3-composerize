<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Sample Extension',
    'description' => 'Extension for testing composer',
    'category' => 'fe',
    'author' => 'Sample Author',
    'author_email' => 'sample@author.com',
    'state' => 'stable',
    'clearCacheOnLoad' => false,
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.5.0-10.4.99',
            'beuser' => '',
            'php' => '7.2.0-7.4.99'
        ],
        'conflicts' => [
            'news' => '8.2.5',
        ],
        'suggests' => [
            "workspaces" => '',
        ]
    ]
];
