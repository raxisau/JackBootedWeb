<?php

require_once ( dirname( __FILE__ ) . '/config.php' );
if ( ( $output = \Jackbooted\Html\WebPage::controller( \App\Commands\CLI::DEF ) ) !== false )
    echo $output;
