<?php
namespace Jackbooted\Cron;

use \Jackbooted\DB\DAO;
use \Jackbooted\Util\Log4PHP;
/**
 * @copyright Confidential and copyright (c) 2017 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */

class CronDAO extends DAO  {
    const STATUS_NEW      = 'NEW';
    const STATUS_RUNNING  = 'RUNNING';
    const STATUS_COMPLETE = 'COMPLETE';

    private static $log;

    /**
     * @return void
     */
    public static function init () {
        $className = __CLASS__;
        self::$log = Log4PHP::logFactory ( $className );
    }

    /**
     * @return void
     */
    public function __construct () {
        $this->db = 'local';
        $this->primaryKey = 'fldCronQueueID';
        $this->tableName = 'tblCronQueue';
        $this->keyFormat = 'CQ0000000';
        $this->tableStructure = <<<SQL
            CREATE TABLE IF NOT EXISTS {$this->tableName} (
              {$this->primaryKey} varchar(11)    NOT NULL default '',
              fldRef varchar(11) NOT NULL DEFAULT '0',
              fldCommand varchar(255) NOT NULL DEFAULT '',
              fldPriority char(3) NOT NULL DEFAULT '0',
              fldStatus enum('NEW','RUNNING','COMPLETE') NOT NULL DEFAULT 'NEW',
              fldRunTime varchar(30) DEFAULT NULL,
              fldReturnValue char(3) DEFAULT NULL,
              fldReturnOutput varchar(255) DEFAULT NULL,
              PRIMARY KEY ({$this->primaryKey})
            )
SQL;

        $this->orm =  [ 'ref'             => 'fldref',
                        'command'         => 'fldCommand',
                        'cmd'             => 'fldCommand',
                        'priority'        => 'fldPriority',
                        'status'          => 'fldStatus',
                        'runTime'         => 'fldRunTime',
                        'result'          => 'fldReturnValue',
                        'message'         => 'fldReturnOutput' ];

        parent::__construct();
    }

    public function getActive ( $ref=null ) {
        $where = $this->orm['status'] . "!='" . self::STATUS_COMPLETE . "'";
        if ( $ref != null ) {
            $where .= ' AND ' . $this->orm['ref'] . '=' . $ref;
        }
        return $this->getRowCount ( $where );
    }

    public function getNew ( $ref=null ) {
        $where = $this->orm['status'] . "='" . self::STATUS_NEW . "'";
        if ( $ref != null ) {
            $where .= ' AND ' . $this->orm['ref'] . '=' . $ref;
        }
        return $this->getRowCount ( $where );
    }

}