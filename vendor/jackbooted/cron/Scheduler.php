<?php

namespace Jackbooted\Cron;

use \Jackbooted\DB\ORM;
use \Jackbooted\Util\Log4PHP;

/**
 * @copyright Confidential and copyright (c) 2022 Jackbooted Software. All rights reserved.
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
    public static function init() {
        self::$log = Log4PHP::logFactory( __CLASS__ );
        self::$dao = new SchedulerDAO ();
    }

    public static function load( $id ) {
        return new Scheduler( self::$dao->oneRow( $id ) );
    }

    public static function getList( $all = false ) {
        if ( $all ) {
            $where = [ 'where' => [] ];
        }
        else {
            $where = [ 'where' => [ 'active' => SchedulerDAO::ACTIVE ] ];
        }

        $table = self::$dao->search( $where );
        return self::tableToObjectList( $table );
    }

    /**
     * @param  $data
     * @return void
     */
    public function __construct( $data ) {
        parent::__construct( self::$dao, $data );
    }

    /**
     * Check if there are any upcoming schedules
     */
    public static function check() {
        $numAdded = 0;

        foreach ( self::getList() as $sheduleItem ) {

            $storedLastRunTime = strtotime( ( $sheduleItem->lastRun == '' ) ? $sheduleItem->start : $sheduleItem->lastRun );
            $previousCalculatedRunTime = CronParser::lastRun( $sheduleItem->cron );

            // This looks at when the item had run. If the stored value is less than
            // the calculated value means that we have past a run period. So need to run
            if ( $storedLastRunTime < $previousCalculatedRunTime ) {

                // Update the run time to now
                $sheduleItem->lastRun = date( 'Y-m-d H:i:s', $previousCalculatedRunTime );
                $sheduleItem->save();

                // Enqueue a new item to run
                $job = new Cron( [ 'ref' => $sheduleItem->id,
                    'cmd' => $sheduleItem->cmd, ] );
                $job->save();
                $numAdded ++;
            }
        }
        return $numAdded;
    }

}
