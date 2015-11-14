<?php

namespace App\Commands;

use \Jackbooted\Config\Cfg;
use \Jackbooted\Config\Config;

class JackCLI extends \Jackbooted\Html\WebPage {

    public static function init () {
        self::$log = \Jackbooted\Util\Log4PHP::logFactory ( __CLASS__ );
    }

    public function version()
    {
        return Cfg::get( 'build_version', 'No Version info' );
    }
    public function checkSystem()
    {
        // Check the queue sizes
        // Check the last readings from the PDUs
        // Checks for stuck jobs
        // Removes old log entries
        return "Ok";
    }


    public function setVar()
    {
        global $argv;
        echo Config::put( $argv[2], $argv[3] );
    }
}
