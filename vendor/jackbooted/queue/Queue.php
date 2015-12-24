<?php
namespace Jackbooted\Queue;
/**
 * @copyright Confidential and copyright (c) 2016 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 *
 */

class Queue extends \Jackbooted\Util\JB {
    const DEF_PRIORITY = 10;
    const DEF_TIME_TO_RUN = 21600; // 6 hrs

    private static $resources = [];
    private static $log = null;

    public static function init () {
        self::$log = \Jackbooted\Util\Log4PHP::logFactory ( __CLASS__ );
    }

    public static function enQueue ( $queueName, $payLoad, $pri=self::DEF_PRIORITY, $delay=0, $ttr=self::DEF_TIME_TO_RUN ) {
        if ( ! isset ( self::$resources[$queueName] ) ) {
            self::$resources[$queueName] = [ 'in', 'out' ];
        }

        if ( ! isset ( self::$resources[$queueName]['in'] ) ) {
            self::$resources[$queueName]['in'] = new \Beanstalk\Client();
            self::$resources[$queueName]['in']->connect();
            self::$resources[$queueName]['in']->useTube( $queueName );
        }
        return self::$resources[$queueName]['in']->put( $pri, $delay, $ttr, $payLoad );
    }

    public static function deQueue ( $queueName ) {
        if ( ! isset ( self::$resources[$queueName] ) ) {
            self::$resources[$queueName] = [ 'in', 'out' ];
        }

        if ( ! isset ( self::$resources[$queueName]['out'] ) ) {
            self::$resources[$queueName]['out'] = new \Beanstalk\Client();
            self::$resources[$queueName]['out']->connect();
            self::$resources[$queueName]['out']->watch( $queueName );
        }

        if ( ( $job = self::$resources[$queueName]['out']->reserve( 0 ) ) === false ) return false;

        self::$resources[$queueName]['out']->delete( $job['id'] );
        return $job['body'];
    }

    public static function disconnect ( $queueName ) {
        if ( isset ( self::$resources[$queueName]['in'] ) ) {
            self::$resources[$queueName]['in']->disconnect();
        }
        if ( isset ( self::$resources[$queueName]['out'] ) ) {
            self::$resources[$queueName]['out']->disconnect();
        }
        unset( self::$resources[$queueName] );
    }

    public function __destruct()
    {
        foreach ( array_keys( self::$resources ) as $queueName ) {
            self::disconnect( $queueName );
        }
    }
}