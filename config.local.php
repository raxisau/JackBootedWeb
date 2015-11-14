<?php
// This file contains application specific
$config['menu'] = [ 'Main'   => '\App\Controllers\JackMain',
                    'Config' => '\App\Controllers\JackConfig',
                  ];

$config['modules']   = [ '\App\Jack' ];

$config['migration'] = [ '\App\Commands\InstallationCLI',
                         '\App\Commands\MigrationsCLI' ];

$config['build_version'] = 'JackBooted Framework 1.0.0 (built: 2015-11-10 17:00:00)';
