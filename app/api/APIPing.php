<?php
namespace App\Mocks;

class APIPing {
    public static function index () {
        $data = [ 'Pong' ];
        return json_encode( $data );
    }
}
