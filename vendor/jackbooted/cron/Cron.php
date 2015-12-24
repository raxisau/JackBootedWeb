<?php
namespace Jackbooted\Cron;

use \Jackbooted\Config\Cfg;
use \Jackbooted\DB\ORM;
use \Jackbooted\Util\Log4PHP;
/**
 * @copyright Confidential and copyright (c) 2016 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */

class Cron extends ORM {
    const BATCH_SIZE = 10;

    private static $log = null;
    private static $dao = null;

    public static $statusList = ['NEW','RUNNING','COMPLETE'];

    /**
     * @return void
     */
    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
        self::$dao = new CronDAO ();
    }

    /**
     * @param  $id
     * @return Cron
     */
    public static function load ( $id ) {
        return new Cron ( self::$dao->getRow ( $id ) );
    }

    public static function getList ( $batchSize=self::BATCH_SIZE ) {
        $table = self::$dao->search (  [ 'where' =>  [ 'status' => CronDAO::STATUS_NEW ],
                                              'limit' => $batchSize,
                                              'order' =>  [ 'priority' ] ] );
        return self::tableToObjectList ( $table );
    }

    /**
     * Generates the html for cron iframe
     */
    public static function iFrame () {
        $cronUrl = Cfg::get ( 'site_url') . '/cron.php';
        $cronHtml = <<<HTML
<iframe src="{$cronUrl}" frameboarder="1" scrolling="yes" width="620" height="100">
    <p>Your browser does not support iframes.</p>
</iframe><br/>
HTML;
        return $cronHtml;
    }

    /**
     * @param  $data
     * @return void
     */
    public function __construct( $data ) {
        parent::__construct ( self::$dao, $data );
    }

    public static function start ( $job ) {
        $job->complete = 0;
        $job->save ();
        return $job;
    }

    public static function end ( $job ) {
        $job->complete = 100;
        $job->save ();
        return $job;
    }

    public static function setStatus ( $job, $percentComplete ) {
        $job->complete = $percentComplete;
        $job->save ();
        return $job;
    }

    public static function add ( $command, $id=0, $priority=0 ) {
        $cronJob = new Cron (  [ 'command'  => $command,
                                 'ref'      => $id,
                                 'status'   => self::STATUS_NEW,
                                 'priority' => $priority ] );
        $cronJob->save ();
        return $cronJob;
    }
}