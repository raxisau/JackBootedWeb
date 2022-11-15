<?php

// This file contains application specific
$config['menu'] = [ 'Main' => '\App\Controllers\JackMain',
    'Config' => '\App\Controllers\JackConfig',
];

$config['modules'] = [ '\App\Jack' ];

$config['migration'] = [ '\App\Commands\InstallationCLI',
    '\App\Commands\MigrationsCLI' ];

$config['def_display'] = '\App\Controllers\JackMain->index()';

$config['build_version'] = 'JackBooted Framework 3.0.1 (built: 2022-11-15 17:00:00)';
$config['tinymce_api'] = 'no-api-key';
