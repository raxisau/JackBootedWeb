<?php

namespace App\Commands;

class CLI extends \Jackbooted\Html\WebPage {
    const DEF = '\App\Commands\CLI->index()';

    private static $commands = [
        'JACK:check_system'   => '\App\Commands\JackCLI->checkSystem()',
        'JACK:version'        => '\App\Commands\JackCLI->version()',
        'JACK:set_var'        => '\App\Commands\JackCLI::setVar()',

        'DB:migrate'          => '\Jackbooted\DB\Migrations::migrate()',
        'DB:initialize'       => '\App\Commands\InstallationCLI::initialize()',
    ];

    private static $helpText = <<<TXT
php jack.php <command>
<command> is one of the following:
    JACK
    ----
    JACK:check_system   - Returns the status of the system queues, etc
    JACK:version        - Current vesion of the system
    JACK:set_var        - Sets a config value in the database

    DB
    ------
    DB:initialize     - Set up the base database
    DB:migrate        - will install the database and install all models

TXT;

    public static function init () {
        self::$log = \Jackbooted\Util\Log4PHP::logFactory ( __CLASS__ );
    }

    public function index()
    {
        global $argv;
        if ( count( $argv ) < 2 ) return $this->help();

        if ( isset( self::$commands[$argv[1]] ) ) {
            return self::execAction ( self::$commands[$argv[1]] );
        }
        else {
            return $this->help();
        }
    }

    public function help() {
        // Check the commands are valid
        foreach ( self::$commands as $cmd ) {
            $parts = preg_split ( '/(->)|(::)/' , $cmd );
            $clazz = $parts[0];

            if ( $clazz != '\\' . get_class( new $clazz ) ) {
                echo "Error: $clazz not found\n";
            }
        }

        return self::$helpText;
    }
}
