<?php
namespace Jackbooted\DB;

use \Jackbooted\Config\Cfg;
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

class MDB extends \Jackbooted\Util\JB {
    /**
     * Default DB is Local
     */
    const DEF = 'mongo';

    // Keep a cache of the connections
    private static $connections = [];

    // Thelast accessed database
    private static $lastDB = null;

    // Keep a log of the number of calls
    private static $callNumber = 0;

    // Logging
    private static $log;
    private static $queryLoggingFunction;
    private static $queryLoggingLevel;
    private static $queryLogFlag;
    private static $errorLoggingFunction;
    private static $errorLoggingLevel;

    /**
     * Set up the statics, sets up the logging level in one place.
     *
     * If you want to change this throughout this class, you can adjust here.
     * @since 1.0
     * @return void
     */
    public static function init() {
        self::$log = Log4PHP::logFactory ( __CLASS__ );

        // Sets up the logging level in one place
        // If you want to change this throughout this class, you can adjust here
        self::$queryLoggingFunction =  [ self::$log, 'debug' ];
        self::$queryLoggingLevel = Log4PHP::DEBUG;
        self::$queryLogFlag = self::$log->isDisplayed ( self::$queryLoggingLevel );

        self::$errorLoggingFunction =  [ self::$log, 'error' ];
        self::$errorLoggingLevel = Log4PHP::ERROR;
    }

    /**
     * Returns the log object so that you can selectively turn on log flags.
     *
     * Eg:
     * <pre>
     *  $dbLogger = DB::getLogger ();
     *  $dbLogger->setClassOutputDevice ( Log4PHP::SCREEN );
     *  $dbLogger->setClassErrorLevel ( Log4PHP::ALL );
     *  DB::init();
     * </pre>
     * @since 1.0
     * @return Log4PHP - returns the log object.
     */
    public static function getLogger () {
        return self::$log;
    }

    public static function addDB($name, $host, $user, $password, $database, $options='', $driver = 'mongodb') {
        Cfg::set($name.'-host', $host);
        Cfg::set($name.'-db', $database);
        Cfg::set($name.'-user', $user);
        Cfg::set($name.'-pass', $password);
        Cfg::set($name.'-options', $options);
        Cfg::set($name.'-driver', $driver);
    }

    private static function connectionFactory ( $db=null ) {

        if ( is_string ( $db ) ) {
            // If this is a string then a key has been passed.
            // The key may have been set up as PDO object or
            // it might be a key from legacy config
            return self::connectionFactoryFromString ( $db );
        }
        else if ( is_object ( $db ) ) {
            // If this is an objecct then it is likely a Mongo object
            self::$lastDB = $db;
            return self::$lastDB;
        }
        else if ( is_array ( $db ) ) {
            // If this is an array then it might be a database information
            return self::connectionFactoryFromArray ( $db );
        }
        else {
            return self::$lastDB;
        }
    }

    private static function connectionFactoryFromString ( $db ) {
        if ( isset ( self::$connections[$db] ) ) {
            self::$lastDB = self::$connections[$db];
            return self::$lastDB;
        }
        else {
            $dbConnection =  [ 'hostname' => Cfg::get ( $db . '-host' ),
                               'dbname'   => Cfg::get ( $db . '-db'      ),
                               'username' => Cfg::get ( $db . '-user' ),
                               'password' => Cfg::get ( $db . '-pass' ),
                               'options'  => Cfg::get ( $db . '-options', '' ),
                               'driver'   => Cfg::get ( $db . '-driver', 'mongodb' ) ];

            if ( $dbConnection['hostname'] != '' ) {
                return self::connectionFactoryFromArray ( $dbConnection );
            }
            else {
                self::logErrorMessage ( 'Unknown DB: ' . $db );
                return false;
            }
        }
    }

    private static function connectionFactoryFromArray ( $db ) {
        if ( ! isset ( $db['driver'] ) ) {
            $db['driver'] = 'mongodb';
        }

        if ( empty( $db['username'] ) && empty ( $db['password'] ) ) {
            $pw = '';
        }
        else {
            $pw = $db['username'] . ':' . $db['password'] . '@';
        }

        $connectionString = $db['driver'] . '://' . $pw . $db['hostname'];

        $keyConn = hash ( 'md4', $connectionString . '*NO-DATABASE*' . $db['username'] . $db['password'] );
        $keyDB   = hash ( 'md4', $connectionString . $db['dbname']   . $db['username'] . $db['password'] );

        try {
            if ( isset( self::$connections[$keyDB] ) ) {
                self::$lastDB = self::$connections[$keyDB];
                return self::$lastDB;
            }
            else if ( isset( self::$connections[$keyConn] ) ) {
                $mongo = self::$connections[$keyConn];
                self::$lastDB = self::$connections[$keyDB] = $mongo->selectDB( $db['dbname'] );
                return self::$lastDB;
            }
            else {
                if ( empty( $db['options'] ) ) {
                    $options = [];
                }
                else {
                    $options = json_decode( $db['options'], true );
                }

                $mongo = self::$connections[$keyConn] = new MongoClient( $connectionString, $options );
                self::$lastDB = self::$connections[$keyDB] = $mongo->selectDB( $db['dbname'] );

                return self::$lastDB;
            }
        }
        catch ( Exception $ex ) {
            self::logErrorMessage ( 'Error Setting up new MongoDB conn: ' . $db['dbname'] . ' - ' . $db['username'] . ' - ' . $ex->getMessage() );
        }
        return false;
    }

    public static function collection ( $dbh, $collection, $log=false ) {
        if ( self::$queryLogFlag || $log ) {
            self::dbg ( $collection );
        }

        if ( ( $dbResource = self::connectionFactory ( $dbh ) ) === false ) {
            return false;
        }

        try {
            return new MongoCollection( $dbResource, $collection );
        }
        catch ( Exception $ex ) {
            return self::logErrorMessage ( 'Trying to get new collection: ' . $collection . ' - ' . $ex->getMessage() );
        }
    }


    private static function logErrorMessage( $message ) {
        //echo $message . self::calculateCallLocation();
        self::$log->error ( $message, self::calculateCallLocation() );
        return false;
    }

    private static function dbg ( $qry, &$params=null ) {
        $msg = self::$callNumber . ':"' . $qry . '"';
        self::$callNumber ++;
        if ( $params != null ) {
            $msg .= ( is_array ( $params ) ) ? join ( ':', $params ) : $params;
        }
        self::$log->debug ( $msg, self::calculateCallLocation() );
    }

    private static function calculateCallLocation ( ) {
        $stack = debug_backtrace ();
        $stackLength = count ( $stack );
        for ( $origin = 1; $origin<$stackLength; $origin++ ) {
            if ( __FILE__ != $stack[$origin]['file'] ) break;
        }

        $fileLocation = basename ( $stack[$origin]['file'] );
        $lineNumber = '(L:' . $stack[$origin]['line'] . ')';
        $origin ++;
        $calledFrom = ( ( isset ( $stack[$origin]['class'] ) ) ? $stack[$origin]['class'] : '' ) .
                      ( ( isset ( $stack[$origin]['type'] ) ) ? $stack[$origin]['type'] : '' ) .
                      ( ( isset ( $stack[$origin]['function'] ) ) ? $stack[$origin]['function'] : '' );
        if ( $calledFrom == '' ) {
            $calledFrom = $fileLocation;
        }

        return $lineNumber . $calledFrom;
    }

    public static function reset() {
        if ( isset( self::$connections ) AND is_array( self::$connections ) AND count( self::$connections ) > 0) {
            foreach ( self::$connections as $db => $connection ) {
                unset( self::$connections[$db] );
            }
        }
        else {
            self::$connections = [];
        }
    }
}
