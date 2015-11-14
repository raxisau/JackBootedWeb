<?php
require_once dirname ( __FILE__ ) . '/config.php';

$requestURI    = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ( $requestMethod ) {
    case 'GET':
        if ( $requestURI == '/api/Ping' ) {
            echo \App\Mocks\APIPing::index();
            exit;
        }

    case 'POST':
        switch ( $requestURI ) {
            case '/api/Ping':
                echo \App\Mocks\APIPing::index();
                exit;
        }
}

echo "Unknown $requestMethod Request: $requestURI";
