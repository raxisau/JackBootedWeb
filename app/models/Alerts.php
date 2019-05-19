<?php
namespace App\Models;

use \Jackbooted\Util\Log4PHP;
use \Jackbooted\DB\ORM;
use \Jackbooted\DB\DB;
use \Jackbooted\Time\Stopwatch;

class Alerts extends ORM {
    const TYPE_DEBUG = 'debug';
    const TYPE_ERROR = 'error';
    const TYPE_INFO  = 'info';
    const TYPE_CRIT  = 'critical';
    public static $typeList = [ self::TYPE_DEBUG, self::TYPE_ERROR, self::TYPE_INFO, self::TYPE_CRIT, ];

    const STATUS_NEW  = 'new';
    const STATUS_SEEN = 'seen';
    const STATUS_FAV  = 'fav';
    public static $statusList = [ self::STATUS_NEW, self::STATUS_SEEN, self::STATUS_FAV, ];

    private static $log = null;
    private static $dao = null;

    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
        self::$dao = new AlertsDAO ();
    }

    public static function debug( $process, $description, $errNum=0 ) {
        self::create([ 'errorID'=> $errNum, 'type'=> self::TYPE_DEBUG, 'process'=> $process, 'description'=> $description, ]);
        self::$log->debug( "({$process}) $description #{$errNum}" );
    }

    public static function error( $process, $description, $errNum=0 ) {
        self::create([ 'errorID'=> $errNum, 'type'=> self::TYPE_ERROR, 'process'=> $process, 'description'=> $description, ]);
        self::$log->error( "({$process}) $description #{$errNum}" );
    }

    public static function info( $process, $description, $errNum=0 ) {
        self::create([ 'errorID'=> $errNum, 'type'=> self::TYPE_INFO, 'process'=> $process, 'description'=> $description, ]);
        self::$log->info( "({$process}) $description #{$errNum}" );
    }

    public static function critical( $process, $description, $errNum=0 ) {
        self::create([ 'errorID'=> $errNum, 'type'=> self::TYPE_CRIT, 'process'=> $process, 'description'=> $description, ]);
        self::$log->fatal( "({$process}) $description #{$errNum}" );
    }

    public static function load( $id ) {
        if ( ( $row = self::$dao->oneRow ( $id ) ) == false ) return false;
        return new Alerts ( $row );
    }

    public static function cleanup( $numDays=5 ) {
        $oneDay = 60 * 60 * 24;

        time() - ( $numDays * $oneDay );
        DB::exec( DB::DEF,
                  'DELETE FROM ' . self::$dao->tableName . ' WHERE fldTimeStamp<?',
                  Stopwatch::timeToDB(time() - ( $numDays * $oneDay ) ) );
    }

    public function __construct( $data ) {
        parent::__construct ( self::$dao, $data );
    }
}