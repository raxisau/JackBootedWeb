<?php
namespace Jackbooted\Util;

/*
 * @copyright Confidential and copyright (c) 2015 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 */

class NetworkUtils extends JB {

    public static function whatIsMyIP () {
        return file_get_contents( 'https://api.ipify.org' );
    }
}