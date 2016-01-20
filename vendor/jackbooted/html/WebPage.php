<?php
namespace Jackbooted\Html;

use \Jackbooted\Config\Cfg;
use \Jackbooted\Forms\Request;
use \Jackbooted\Security\Privileges;
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

class WebPage extends \Jackbooted\Util\JB {
    const ACTION = '_ACT';
    const SAVE_URL = '_SAVEURL';

    protected static $log;

    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
    }

    public static function controller ( $default='', $actionKey=self::ACTION ) {
        $action = Request::get ( $actionKey, $default );
        if ( ! isset( $action ) || $action == false ) return false;

        if ( ( $modifiedAction = self::checkPriviliages ( $action ) ) === false ) {
            return 'You do not have priviliages to perform action: ' . $action;
        }
        else {
            $action = $modifiedAction;
        }

        return self::execAction ( $action );
    }
    
    protected static function execAction ( $action ) {
        if ( strpos ( $action, '::' ) !== false ) {
            eval ( '$html = ' . $action . ';' );
        }
        else if ( strpos ( $action, '->' ) !== false ) {
            list ( $clazz, $rest ) = explode ( '->', $action );
            $obj = new $clazz ();
            eval ( '$html = $obj->' . $rest . ';' );
        }
        else {
            $cName = ( function_exists ( 'get_called_class' ) ) ? get_called_class() : __CLASS__;
            $object = new $cName ();

            if ( method_exists ( $object, $action ) ) {
                $html = $object->$action ();
            }
            else {
                $html = $object->index ();
            }
        }
        return $html;
    }

    private static function checkPriviliages ( $action ) {
        if ( ! Cfg::get ( 'check_priviliages', false ) ) return $action;

        if ( ( $loginAction = Privileges::access ( $action ) ) === false ) return false;
        if ( is_string ( $loginAction ) && isset ( $_SERVER["REQUEST_URI"] ) ) {
            Request::set ( self::SAVE_URL, $_SERVER["REQUEST_URI"] );
            $action = $loginAction;
        }
        return $action;
    }

    public function __construct () {
        parent::__construct();
    }

    public function index () {
        return '<pre>' . var_export ( $_REQUEST, false ) . '</pre>';
    }

    public function blank () {
        return '';
    }

    public function __call ( $name, $arguments ) {
        $fName = Cfg::get ( 'site_path' ) . '/' . $name . '.html';
        if ( file_exists ( $fName ) ) {
            return file_get_contents ( $fName );
        }
        else {
            return 'Unknown Method Call: ' . $name;
        }
    }
}
