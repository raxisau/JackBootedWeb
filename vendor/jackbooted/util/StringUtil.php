<?php
namespace Jackbooted\Util;

/**
 * @copyright Confidential and copyright (c) 2015 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 */

class StringUtil extends \Jackbooted\Util\JB {

    public static function unitsFormat ( $num, $units, $msg='' ) {
        return $msg . ( ( $msg == '' ) ? '' : ' ' ) . $num . ' ' . $units . self::plural( $num );
    }

    public static function plural ( $num ) {
        return ( $num == 1 ) ? '' : 's';
    }
}
