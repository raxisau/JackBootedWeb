<?php

require_once ( __DIR__ . '/config.php' );
if ( ( $output = \Jackbooted\Html\WebPage::controller( \App\Commands\CLI::DEF ) ) !== false )
    echo $output;
