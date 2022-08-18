<?php

namespace Jackbooted\Config;

use \Jackbooted\Forms\Request;
use \Jackbooted\Forms\Response;
use \Jackbooted\G;
use \Jackbooted\Html\JS;
use \Jackbooted\Html\Tag;
use \Jackbooted\Security\CSRFGuard;
use \Jackbooted\Security\TamperGuard;
use \Jackbooted\Security\TimeGuard;
use \Jackbooted\Util\Log4PHP;

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
        self::setUpDebugFriendlyClassSwitches();
        self::setUpSession();
        self::ensureNoForgery();
        self::setErrorLevel();
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
        if ( ( $tz = G::get( 'fldTimeZone', false ) ) !== false ) {
            date_default_timezone_set( $tz );
        }
        else if ( ( $tz = self::get( 'timezone', false ) ) !== false ) {
            date_default_timezone_set( $tz );
        }

        $timeStamp = time();
        self::set( 'local_timestamp', $timeStamp );
        self::set( 'local_date_time', strftime( '%Y-%m-%d %H:%M:%S', $timeStamp ) );
        self::set( 'local_date', strftime( '%Y-%m-%d', $timeStamp ) );
        self::set( 'local_time', strftime( '%H:%M', $timeStamp ) );
        self::set( 'local_date_array', getdate( $timeStamp ) );
    }

    public static function siteUrl() {
        return self::get( 'site_url' );
    }

    private static function checkForMaintenance() {
        if ( !self::get( 'maintenance' ) ) {
            return;
        }
        $maint = Cfg::get( 'maintenance_url', self::siteUrl() . '/maintenance.php' );
        header( 'Location: ' . $maint );
        exit;
    }

    private static function ensureNoForgery() {
        if ( !Cfg::get( 'jb_forgery_check', true ) ) {
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
        if ( in_array( basename( $fileName ), Cfg::get( 'exempt', [] ) ) )
            return;

        // Add the known request variables to TamperGuard
        foreach ( Cfg::get( 'known', [] ) as $val ) {
            TamperGuard::known( $val );
        }
        $message = null;

        if ( ( $tg = TimeGuard::check() ) !== TimeGuard::NOGUARD ) {
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
        else if ( ( $reqChk = Request::check() ) !== true ) {
            $reqChk = str_replace( '%', '%%', $reqChk );
            $message = <<<HTML
                Invalid or expired request (URL Error - $reqChk)<br/>
                %s has detected changes in the URL.<br/>
                Please do not manually edit URL (support %s).<br/>
                You will be <a href="%s">redirected</a> in %s seconds
                <meta HTTP-EQUIV="REFRESH" content="%s; url=%s">
HTML;
        }
        else if ( !CSRFGuard::check() ) {
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

            if ( ( $location = Cfg::get( 'index' ) ) == '' ) {
                $location = Cfg::siteUrl() . '/index.php';
            }

            echo sprintf( $message, Cfg::get( 'version' ), Cfg::get( 'boss' ), $location, $seconds, $seconds, $location );
            exit;
        }
    }

    private static function setUpSession() {
        if ( ! Cfg::get( 'jb_db' ) ) {
            return;
        }

        \Jackbooted\Admin\Login::initSession();

        // See if we can log the user in
        if ( ! \Jackbooted\Admin\Login::loadPreferencesFromCookies() ) {
            if ( G::isLoggedIn() ) {
                \Jackbooted\Admin\Login::logOut();
            }
        }
    }


    private static function setUpDebugFriendlyClassSwitches() {
        if ( !self::get( 'debug' ) ) {
            return;
        }

        // Add here if necessary
    }

    private static function preLoadUsedClasses() {
        $dir = dirname( dirname( __FILE__ ) );

        $filesToLoad = [ $dir . '/util/JB.php',
            $dir . '/util/Log4PHP.php',
            $dir . '/util/PHPExt.php',
            $dir . '/util/AutoLoader.php',
            $dir . '/util/ClassLocator.php',
            $dir . '/time/Stopwatch.php' ];

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
        Log4PHP::init( Log4PHP::ALL );
        Log4PHP::setOutput( Log4PHP::FILE );
        self::$log = Log4PHP::logFactory( __CLASS__ );
        self::$log->debug( __METHOD__ . 'Logging set up' );

//        $errorLogLocation = ini_get( 'error_log' );
//        if ( !isset( $errorLogLocation ) || $errorLogLocation == false ) {
//            ini_set( 'error_log', '/dev/stdout' );
//        }
//
//        $inDevMode = self::get( 'debug' );
//
//        Log4PHP::init( ( $inDevMode ) ? Log4PHP::DEBUG : Log4PHP::ERROR  );
//        Log4PHP::setOutput( Log4PHP::FILE );
//        self::$log = Log4PHP::logFactory( __CLASS__ );
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

    public static function setErrorLevel() {
        $errMode = self::get( 'jb_error_mode' );

        $level = ( $errMode ) ? ( E_ALL | E_STRICT ) : 0;

        error_reporting( $level );
        ini_set( 'display_errors', ( $errMode ) ? '1' : '0'  );
        self::$errorLevel = $level;
    }

    public static function errorHandler( $errno, $errstr, $errfile, $errline ) {
        if ( !preg_match( '/^.*\\/3rdparty\\/.*$/', $errfile ) ) {
            self::$log->error( "{$errno}-{$errstr}: {$errfile}({$errline})" );
        }

        switch ( $errno ) {
            case E_STRICT:
            case E_USER_WARNING:
                return;

            default:
                if ( self::get( 'debug' ) ) {
                    $errMsg = sprintf( '(!) Fatal error: %s in %s on line: %s', $errstr, $errfile, $errline );

                    $html = Tag::table( [ 'cellpadding' => 3, 'cellspacing' => 0, 'border' => 1 ] ) .
                            Tag::tr( [ 'bgcolor' => 'silver' ] ) .
                            Tag::th( [ 'colspan' => 4, 'align' => 'left' ] ) . $errMsg . Tag::_th() .
                            Tag::_tr() .
                            Tag::tr( [ 'bgcolor' => 'silver' ] ) .
                            Tag::th() . 'Stk' . Tag::_th() .
                            Tag::th() . 'File' . Tag::_th() .
                            Tag::th() . 'Line' . Tag::_th() .
                            Tag::th() . 'Function' . Tag::_th() .
                            Tag::_tr();

                    foreach ( debug_backtrace() as $idx => $row ) {
                        if ( $idx == 0 ) {
                            continue;
                        }

                        if ( isset( $row['file'] ) ) {
                            $file = basename( $row['file'] );
                            $title = $row['file'];
                        }
                        else {
                            $file = '&nbsp;';
                            $title = ' ';
                        }

                        $line = ( isset( $row['line'] ) ) ? $row['line'] : '&nbsp;';

                        $function = $row['function'];
                        if ( isset( $row['class'] ) ) {
                            $function = $row['class'] . $row['type'] . $function;
                        }

                        $style = ( ( $idx % 2 ) == 0 ) ? [] : [ 'bgcolor' => 'yellow' ];
                        $html .= Tag::tr( $style ) .
                                Tag::td() . $idx . Tag::_td() .
                                Tag::td( [ 'title' => $title ] ) . $file . Tag::_td() .
                                Tag::td() . $line . Tag::_td() .
                                Tag::td() . $function . Tag::_td() .
                                Tag::_tr();
                    }

                    $html .= Tag::_table();
                    $html = str_replace( [ "\n", "'" ], [ '', "\\'" ], $html );
                    $url = self::siteUrl() . '/ajax.php?' . Response::factory()->action( '\Jackbooted\Html\WebPage->blank()' )->toUrl();

                    $js = <<<JS
                    $().ready(function() {
                        newWindow = window.open ( '$url','Error Output','height=300,width=700');
                        var tmp = newWindow.document;
                        tmp.write ( '<html><head><title>$errMsg</title>' );
                        tmp.write ( '<style type="text/css">' );
                        tmp.write ( '</style>');
                        tmp.write ( '</head><body>');
                        tmp.write ( '$html' );
                        tmp.write ( '</body></html>');
                        tmp.close();
                    });
JS;
                    echo JS::library( JS::JQUERY ) .
                    JS::javaScript( $js ) .
                    $errMsg;
                }
                break;
        }
    }

}
