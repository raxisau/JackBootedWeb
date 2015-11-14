<?php
namespace Jackbooted\Cron;

use \Jackbooted\DB\DAO;
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

class SchedulerDAO extends DAO  {
    const ACTIVE = 'Yes';

    private static $log;

    /**
     * @return void
     */
    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
    }

    /**
     * @return void
     */
    public function __construct () {
        $this->db = 'local';
        $this->primaryKey = 'fldSchedulerID';
        $this->tableName = 'tblScheduler';
        $this->tableStructure = <<<SQL
            CREATE TABLE IF NOT EXISTS {$this->tableName} (
              {$this->primaryKey} varchar(11)    NOT NULL default '',
              fldCommand varchar(255) NOT NULL DEFAULT '',
              fldActive enum('Yes','No') NOT NULL DEFAULT 'Yes',
              fldStartTime varchar(40) NOT NULL DEFAULT '',
              fldCron varchar(100) NOT NULL DEFAULT '',
              fldLastRun varchar(40) NOT NULL DEFAULT '',
              PRIMARY KEY ({$this->primaryKey})
            );
SQL;

        $this->orm =  [ 0                 => $this->primaryKey,
                        'id'              => $this->primaryKey,
                        1                 => 'fldCommand',
                        'command'         => 'fldCommand',
                        'cmd'             => 'fldCommand',
                        2                 => 'fldActive',
                        'active'          => 'fldActive',
                        3                 => 'fldStartTime',
                        'start'           => 'fldStartTime',
                        4                 => 'fldCron',
                        'cron'            => 'fldCron',
                        5                 => 'fldLastRun',
                        'lastRun'         => 'fldLastRun',
                ];

        parent::__construct();
    }
}