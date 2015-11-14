<?php
namespace App\Commands;

use \Jackbooted\Config\Cfg;
use \Jackbooted\DB\DB;

class InstallationCLI extends \Jackbooted\Html\WebPage {

    public static function init () {
        self::$log = \Jackbooted\Util\Log4PHP::logFactory ( __CLASS__ );
    }

    public static function initialize()
    {
        $dbType     = Cfg::get( 'local-driver' );

        switch ( $dbType ) {
            case 'sqlite':
                $dbFileName = Cfg::get( 'local-host' );
                echo "Checking that the file $dbFileName exists\n";

                if ( file_exists( $dbFileName ) ) {
                    echo "Database exists ($dbFileName)\n";
                }
                else {
                    echo "Creating empty database\n";
                    touch( $dbFileName );
                }
                break;

            case 'mysql':
                $fldHostName = Cfg::get( 'local-host' );
                $fldDBName   = Cfg::get( 'local-db' );
                $fldUsername = Cfg::get( 'local-user' );
                $fldPassword = Cfg::get( 'local-pass' );
                try {
                    $dbh = new \PDO( "mysql:host=$fldHostName", $fldUsername, $fldPassword );
                    $dbh->exec( "CREATE DATABASE IF NOT EXISTS $fldDBName" ) or die( print_r( $dbh->errorInfo(), true ) );
                }
                catch (PDOException $e) {
                    die( "DB ERROR: ". $e->getMessage() );
                }
                break;

            default:
                die( "Unsupported DB Type: $dbType" );
        }

        if ( count ( \Jackbooted\DB\DBMaintenance::getTableList() ) != 0 ) {
            die( "Database already seems to be set up." );
        }

        // Put in the base data
        $sqlFileName = Cfg::get ( 'tmp_path' ) . '/base_database.sql';
        if ( file_exists( $sqlFileName ) ) {
            echo "Running the commands in $sqlFileName against the database\n";
            foreach ( explode( ';', file_get_contents( $sqlFileName ) ) as $statement ) {
                DB::exec( DB::DEF, $statement );
            }
        }
        else {
            echo "Base Database file does not exists ($sqlFileName) aborting\n";
        }
        return '';
    }

    public static function migrate2015_10_02_00_00()
    {
        echo "audititing Table - AlertsDAO\n";
        ( new \App\Models\AlertsDAO )->auditTable();
    }
}
