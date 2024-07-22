<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'SUMM AI Translate',
    'description' => 'Translate German text into Plain German (Leichte Sprache) via SUMM AI NLP-model. SUMM AI token required!',
    'category' => 'be',
    'author' => 'Konstantin Kuklin',
    'author_email' => 'konstantin.kuklin@th-brandenburg.de',
    'state' => 'experimental',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
