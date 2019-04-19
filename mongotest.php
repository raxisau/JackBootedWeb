<?php

use \Jackbooted\Config\Cfg;
use \Jackbooted\Html\JS;

require_once ( dirname( __FILE__ ) . '/config.php' );

$username = 'jackadmin';
$password = 'ru1c1hYfYzdNysle';

$client = new \MongoDB\Client(
        "mongodb://" .
        "${username}:${password}@" .
        "jackcluster0-shard-00-00-qc1m4.mongodb.net:27017," .
        "jackcluster0-shard-00-01-qc1m4.mongodb.net:27017," .
        "jackcluster0-shard-00-02-qc1m4.mongodb.net:27017/jackbooted?ssl=true&replicaSet=JackCluster0-shard-0&authSource=admin" );

$collection = $client->jackbooted->jackcollect;

$result = $collection->find( [ 'name' => 'Brett Dutton' ] );

foreach ( $result as $entry ) {
    print_r( $entry );
}


$result = $collection->find( [] );

foreach ( $result as $entry ) {
    print_r( $entry );
}

