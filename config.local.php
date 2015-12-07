<?php
// This file contains application specific
$config['menu'] = [ 'Main'   => '\App\Controllers\JackMain',
                    'Config' => '\App\Controllers\JackConfig',
                  ];

$config['modules']   = [ '\App\Jack' ];

$config['migration'] = [ '\App\Commands\MigrationsCLI' ];

$config['def_display'] = '\App\Controllers\JackMain->index()';

$config['build_version'] = 'JackBooted Framework 1.0.0 (built: 2015-11-15 17:00:00)';
