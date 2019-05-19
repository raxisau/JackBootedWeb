<?php
namespace App\Models;
use \Jackbooted\DB\DB;
use \Jackbooted\Util\Log4PHP;
use \Jackbooted\DB\DAO;

class AlertsDAO extends DAO  {
    private static $log;

    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
    }
    public function __construct () {
        $this->db = DB::DEF;
        $this->primaryKey = 'fldModJackAlertID';
        $this->keyFormat = 'AL0000000';
        $this->tableName = 'tblModJackAlert';
        $this->tableStructure = <<<SQL
            CREATE TABLE {$this->tableName} (
              {$this->primaryKey} char(11) NOT NULL,
              fldErrorID char(11) NOT NULL,
              fldType char(6) NOT NULL,
              fldProcess varchar(50) NOT NULL,
              fldDescription varchar(200) NOT NULL,
              fldStatus char(6) NOT NULL DEFAULT 'new',
              fldTimeStamp datetime NOT NULL DEFAULT current_timestamp,
              PRIMARY KEY ({$this->primaryKey})
            );
SQL;

        /* This is the mapping between the object names and the column names
         * Please note that you can access data as different names
         */
        $this->orm = [ 'errorID'     => 'fldErrorID',
                       'error_id'    => 'fldErrorID',
                       'type'        => 'fldType',
                       'process'     => 'fldProcess',
                       'desc'        => 'fldDescription',
                       'description' => 'fldDescription',
                       'status'      => 'fldStatus',
                     ];

        $this->titles = [ 'fldDescription' => 'Alert Description',
                        ];

        parent::__construct();
    }
}
