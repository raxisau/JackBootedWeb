<?php

require_once __DIR__ . '/config.php';

$requestURI = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ( $requestMethod ) {
    case 'GET':
        if ( $requestURI == '/jack/api/Ping' ) {
            echo \App\API\APIPing::index();
            exit;
        }
        break;

    case 'POST':
        switch ( $requestURI ) {
            case '/jack/api/Ping':
                echo \App\API\APIPing::index();
                exit;
        }
        break;
}

echo "Unknown $requestMethod Request: $requestURI";
