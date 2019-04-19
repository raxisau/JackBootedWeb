<?php

// This file contains application specific
$config['menu'] = [ 'Main' => '\App\Controllers\JackMain',
    'Config' => '\App\Controllers\JackConfig',
];

$config['modules'] = [ '\App\Jack' ];

$config['migration'] = [ '\App\Commands\InstallationCLI',
    '\App\Commands\MigrationsCLI' ];

$config['def_display'] = '\App\Controllers\JackMain->index()';

$config['build_version'] = 'JackBooted Framework 1.0.1 (built: 2016-01-10 17:00:00)';
