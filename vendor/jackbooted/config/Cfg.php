<?php

namespace Jackbooted\Config;

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
class Cfg {

    private static $errorLevel;
    private static $config;
    private static $log;

    public static function init( &$cfg ) {
        self::$config = $cfg;

        date_default_timezone_set( 'UTC' );
        setlocale( LC_MONETARY, 'en_US' );
        ini_set( 'short_open_tag', '1' );

        self::checkForMaintenance();
        self::preLoadUsedClasses();
        self::setUpLogging();
        self::setUpAutoLoader();
        self::setUpSession();
        self::ensureNoForgery();
        self::setUpDates();
    }

    public static function get( $key = null, $def = '' ) {
        if ( $key == null ) {
            return self::$config;
        }
        
        if ( !isset( self::$config[$key] ) ) {
            return $def;
        }
        return self::$config[$key];
    }

    public static function set( $key, $value ) {
        $returnValue = ( isset( self::$config[$key] ) ) ? self::$config[$key] : '';
        self::$config[$key] = $value;
        return $returnValue;
    }

    public static function setUpDates() {
        if ( ( $tz = \Jackbooted\G::get( 'fldTimeZone', false ) ) !== false ) {
            date_default_timezone_set( $tz );
        }
        else if ( ( $tz = self::get( 'timezone', false ) ) !== false ) {
            date_default_timezone_set( $tz );
        }

        $timeStamp = time();
        self::set( 'local_timestamp',  $timeStamp );
        self::set( 'local_date_time',  strftime( '%Y-%m-%d %H:%M:%S', $timeStamp ) );
        self::set( 'local_date',       strftime( '%Y-%m-%d', $timeStamp ) );
        self::set( 'local_time',       strftime( '%H:%M', $timeStamp ) );
        self::set( 'local_date_array', getdate( $timeStamp ) );
    }

    public static function siteUrl() {
        return self::get( 'site_url' );
    }

    private static function checkForMaintenance() {
        if ( !self::get( 'maintenance' ) ) {
            return;
        }
        $maint = self::get( 'maintenance_url', self::siteUrl() . '/maintenance.php' );
        header( 'Location: ' . $maint );
        exit;
    }

    private static function ensureNoForgery() {
        self::$log->trace( 'Entering: ' . __METHOD__ );
        if ( !self::get( 'jb_forgery_check', true ) ) {
            self::$log->trace( 'Exiting: ' . __METHOD__ );
            return;
        }

        // No checking if there are not any arguments
        if ( count( $_POST ) == 0 && count( $_GET ) == 0 ) {
            return;
        }

        // Check if the current script is exempt from forgery check
        $fileName = '';
        if ( isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
            $fileName = $_SERVER['SCRIPT_FILENAME'];
        }
        else if ( isset( $_SERVER['argv'][0] ) ) {
            $fileName = $_SERVER['argv'][0];
        }
        if ( in_array( basename( $fileName ), self::get( 'exempt', [] ) ) ) {
            self::$log->trace( 'Exiting: ' . __METHOD__ );
            return;
        }

        // Add the known request variables to TamperGuard
        foreach ( self::get( 'known', [] ) as $val ) {
            \Jackbooted\Security\TamperGuard::known( $val );
        }
        $message = null;

        if ( ( $tg = \Jackbooted\Security\TimeGuard::check() ) !== \Jackbooted\Security\TimeGuard::NOGUARD ) {
            if ( $tg !== true ) {
                $message = <<<HTML
                    Invalid AJAX Request ($tg)<br/>
                    %s has detected changes in the URL.<br/>
                    Please do not manually edit URL or reuse URL (support %s).<br/>
                    You will be <a href="%s">redirected</a> in %s seconds
                    <meta HTTP-EQUIV="REFRESH" content="%s; url=%s">
HTML;
            }
        }
        else if ( ( $reqChk = \Jackbooted\Forms\Request::check() ) !== true ) {
            $reqChk = str_replace( '%', '%%', $reqChk );
            $message = <<<HTML
                Invalid or expired request (URL Error - $reqChk)<br/>
                %s has detected changes in the URL.<br/>
                Please do not manually edit URL (support %s).<br/>
                You will be <a href="%s">redirected</a> in %s seconds
                <meta HTTP-EQUIV="REFRESH" content="%s; url=%s">
HTML;
        }
        else if ( ! \Jackbooted\Security\CSRFGuard::check() ) {
            $message = <<<HTML
                Invalid Request (CSRF error)<br/>
                %s has detected re-submission or form tampering.<br/>
                please contact support %s<br/>
                You will be <a href="%s">redirected</a> in %s seconds
                <meta HTTP-EQUIV="REFRESH" content="%s; url=%s">
HTML;
        }

        if ( $message != null ) {
            $seconds = '5';

            if ( ( $location = self::get( 'index' ) ) == '' ) {
                $location = self::siteUrl() . '/index.php';
            }

            self::$log->trace( 'Exiting: ' . __METHOD__ );
            echo sprintf( $message, self::get( 'version' ), self::get( 'boss' ), $location, $seconds, $seconds, $location );
            exit;
        }
        
        self::$log->trace( 'Exiting: ' . __METHOD__ );
    }

    private static function setUpSession() {
        if ( ! self::get( 'jb_db' ) ) {
            return;
        }

        self::initSession();

        // See if we can log the user in
        if ( ! \Jackbooted\Admin\Login::loadPreferencesFromCookies() ) {
            if ( \Jackbooted\G::isLoggedIn() ) {
                \Jackbooted\Admin\Login::logOut();
            }
        }
    }

    public static function initSession() {
        self::$log->trace( 'Entering: ' . __METHOD__ );
        if ( ! isset( $_SESSION ) ) {
            session_start();
        }
        if ( !isset( $_SESSION[\Jackbooted\G::SESS] ) ) {
            $_SESSION[\Jackbooted\G::SESS] = [];
        }
        self::$log->trace( 'Exiting ' . __METHOD__ );
    }

    private static function preLoadUsedClasses() {
        $dir = dirname( __DIR__ );

        $filesToLoad = [
            $dir . '/util/JB.php',
            $dir . '/util/Log4PHP.php',
            $dir . '/util/PHPExt.php',
            $dir . '/util/AutoLoader.php',
            $dir . '/util/ClassLocator.php',
            $dir . '/time/Stopwatch.php',
            $dir . '/G.php',
        ];

        foreach ( $filesToLoad as $fileName ) {
            if ( file_exists( $fileName ) ) {
                require_once $fileName;
            }
        }
    }

    private static function setUpAutoLoader() {
        \Jackbooted\Util\AutoLoader::init();
        \Jackbooted\Time\Stopwatch::init();
        \Jackbooted\Util\ClassLocator::init( self::get( 'class_path' ) );
    }

    private static function setUpLogging() {
        $inDevMode = self::get( 'debug' );

        \Jackbooted\Util\Log4PHP::init( ( $inDevMode ) ? \Jackbooted\Util\Log4PHP::DEBUG : \Jackbooted\Util\Log4PHP::ERROR );
        \Jackbooted\Util\Log4PHP::setOutput( \Jackbooted\Util\Log4PHP::FILE );
        self::$log = \Jackbooted\Util\Log4PHP::logFactory( __CLASS__ );
    }

    public static function turnOffErrorHandling() {
        $oldErrorLevel = error_reporting( 0 );
        $oldDisplayErrors = ini_set( 'display_errors', '0' );
        return [ $oldErrorLevel, $oldDisplayErrors ];
    }

    public static function turnOnErrorHandling( $oldValues ) {
        error_reporting( $oldValues[0] );
        ini_set( 'display_errors', $oldValues[1] );
    }
}
