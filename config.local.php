<?php

// This file contains application specific
$config['menu'] = [ 'Main' => '\App\Controllers\JackMain',
    'Config' => '\App\Controllers\JackConfig',
];

$config['modules'] = [ '\App\Jack' ];

$config['migration'] = [ '\App\Commands\InstallationCLI',
    '\App\Commands\MigrationsCLI' ];

$config['def_display'] = '\App\Controllers\JackMain->index()';

$config['build_version'] = 'JackBooted Framework 2.0.1 (built: 2022-06-20 17:00:00)';
$config['tinymce_api'] = 'no-api-key';
