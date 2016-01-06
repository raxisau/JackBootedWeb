<?php
namespace Jackbooted\DB;

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

/**
 */
abstract class ORM extends \Jackbooted\Util\JB {
    const UPDATE = 'update';
    const INSERT = 'insert';

    private static $log;

    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
    }

    public static function create ( $data ) {
        if ( function_exists ( 'get_called_class' ) ) {
            $clazz = get_called_class ();
        }
        else {
            $bt = debug_backtrace ();
            $clazz = $bt[1]['class'];
        }

        $obj = new $clazz ( $data );
        $obj->save();
        return $obj;
    }

    public static function factory ( $data ) {
        if ( function_exists ( 'get_called_class' ) ) {
            $clazz = get_called_class ();
        }
        else {
            $bt = debug_backtrace ();
            $clazz = $bt[1]['class'];
        }

        return new $clazz ( $data );
    }

    protected static function tableToObjectList ( $table ) {
        if ( function_exists ( 'get_called_class' ) ) {
            $clazz = get_called_class ();
        }
        else {
            $bt = debug_backtrace ();
            $clazz = $bt[1]['class'];
        }

        $objList =  [];
        foreach ( $table as $row ) {
            $ormObject = new $clazz ( $row );
            $objList[$ormObject->id] = $ormObject;
        }
        return $objList;
    }

    protected  $data;
    private    $dao;

    public function __construct ( DAO $dao, $data ) {
        parent::__construct();
        $this->dao = $dao;
        $this->data = $this->dao->objToRel ( $data );
    }

    public function __get ( $key ) {
        if ( isset ( $this->dao->orm[$key] ) ) {
            $key = $this->dao->orm[$key];
        }
        return $this->data[$key];
    }

    public function __set ( $key, $value ) {
        if ( isset ( $this->dao->orm[$key] ) ) {
            $key = $this->dao->orm[$key];
        }
        $this->data[$key] = $value;
    }

    public function getData ( ) {
        return $this->data;
    }

    public function save () {
        if ( isset ( $this->data[$this->dao->primaryKey] ) ) {
            $where =  [ $this->dao->primaryKey => $this->data[$this->dao->primaryKey] ];
            $data = array_merge ( $this->data );
            unset ( $data[$this->dao->primaryKey] );
            $this->dao->update ( $data, $where );
            return self::UPDATE;
        }
        else {
            $this->data[$this->dao->primaryKey] = $this->dao->insert ( $this->data );
            return self::INSERT;
        }
    }
    public function delete () {
        if ( isset ( $this->data[$this->dao->primaryKey] ) ) {
            return $this->dao->delete ( [ $this->dao->primaryKey => $this->data[$this->dao->primaryKey] ] );
        }
        else {
            return $this->dao->delete ( $this->data );
        }
    }
}
