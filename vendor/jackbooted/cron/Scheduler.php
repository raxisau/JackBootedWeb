<?php
namespace Jackbooted\Cron;

use \Jackbooted\DB\ORM;
use \Jackbooted\Util\Log4PHP;
/**
 * @copyright Confidential and copyright (c) 2015 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */

class Scheduler extends ORM {

    private static $log = null;
    private static $dao = null;

    /**
     * @return void
     */
    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
        self::$dao = new SchedulerDAO ();
    }

    public static function load ( $id ) {
        return new Scheduler ( self::$dao->oneRow ( $id ) );
    }

    public static function getList ( $all=false ) {
        if ( $all ) {
            $where =  [ 'where' =>  [ ] ];
        }
        else {
            $where =  [ 'where' =>  [ 'active' => SchedulerDAO::ACTIVE ] ];
        }

        $table = self::$dao->search ( $where );
        return self::tableToObjectList ( $table );
    }

    /**
     * @param  $data
     * @return void
     */
    public function __construct( $data ) {
        parent::__construct ( self::$dao, $data );
    }

    /**
     * Check if there are any upcoming schedules
     */
    public static function check () {
        $numAdded = 0;

        foreach ( self::getList ( true ) as $sheduleItem ) {

            if ( ! isset( $sheduleItem->lastRun ) || $sheduleItem->lastRun == false ) {
                $lastRunTime = strtotime( $sheduleItem->start );
            }
            else {
                $lastRunTime = strtotime( $sheduleItem->lastRun );
            }

            $thisRunTime = CronParser::lastRun( $sheduleItem->cron );
            if ( $thisRunTime > $lastRunTime ) {
                $sheduleItem->lastRun = date ( 'Y-m-d H:i', $thisRunTime );
                $sheduleItem->save ();

                $job = new Cron (  [ 'ref' => $sheduleItem->id,
                                     'cmd' => $sheduleItem->cmd, ] );
                $job->save ();
                $numAdded ++;
            }
        }
        return $numAdded;
    }

}
