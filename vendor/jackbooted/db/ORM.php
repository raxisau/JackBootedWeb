<?php

namespace Jackbooted\DB;

use \Jackbooted\Forms\Request;

/**
 * @copyright Confidential and copyright (c) 2019 Jackbooted Software. All rights reserved.
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

    public static function init() {
    }

    public static function create( $data ) {
        if ( function_exists( 'get_called_class' ) ) {
            $clazz = get_called_class();
        }
        else {
            $bt = debug_backtrace();
            $clazz = $bt[1]['class'];
        }

        $obj = new $clazz( $data );

        // remove the primary key from the update
        if ( isset( $obj->data[$obj->dao->primaryKey] ) ) {
            unset( $obj->data[$obj->dao->primaryKey] );
        }

        return $obj->save();
    }

    public static function factory( $data ) {
        if ( function_exists( 'get_called_class' ) ) {
            $clazz = get_called_class();
        }
        else {
            $bt = debug_backtrace();
            $clazz = $bt[1]['class'];
        }

        return new $clazz( $data );
    }

    protected static function tableToObjectList( $table ) {
        if ( function_exists( 'get_called_class' ) ) {
            $clazz = get_called_class();
        }
        else {
            $bt = debug_backtrace();
            $clazz = $bt[1]['class'];
        }

        $objList = [];
        foreach ( $table as $row ) {
            $ormObject = new $clazz( $row );
            $objList[$ormObject->id] = $ormObject;
        }
        return $objList;
    }

    protected $data;
    protected $dirty;
    private   $dao;

    public abstract static function load( $id );

    public function __construct( DAO $dao, $data ) {
        parent::__construct();
        $this->dao = $dao;
        $this->data = $this->dao->objToRel( $data );
        $this->clearDirty();
    }

    public function clearDirty() {
        $this->dirty = array_fill_keys( array_keys( $this->data ), 0 );
    }

    public function __get( $key ) {
        if ( isset( $this->dao->orm[$key] ) ) {
            $key = $this->dao->orm[$key];
        }
        return $this->data[$key];
    }

    public function __set( $key, $value ) {
        if ( isset( $this->dao->orm[$key] ) ) {
            $key = $this->dao->orm[$key];
        }

        $this->dirty[$key] = 1; // Set this to be updated
        $this->data[$key] = $value;
    }

    public function getData() {
        return $this->data;
    }

    public function save() {
        if ( isset( $this->data[$this->dao->primaryKey] ) ) {
            $where = [ $this->dao->primaryKey => $this->data[$this->dao->primaryKey] ];
            $data = array_merge( $this->data );

            // remove the primary key from the update
            unset( $data[$this->dao->primaryKey] );

            // Do not update any values that have not been changed
            foreach ( $this->dirty as $key => $value ) {
                if ( $value != 1 )
                    unset( $data[$key] );
            }

            $this->dao->update( $data, $where );
            $this->clearDirty();
            return self::UPDATE;
        }
        else {
            $this->data[$this->dao->primaryKey] = $this->dao->insert( $this->data );
            $this->clearDirty();
            return self::INSERT;
        }
    }

    public function delete() {
        if ( isset( $this->data[$this->dao->primaryKey] ) ) {
            return $this->dao->delete( [ $this->dao->primaryKey => $this->data[$this->dao->primaryKey] ] );
        }
        else {
            return $this->dao->delete( $this->data );
        }
    }

    public function copyToRequest() {
        foreach ( $this->getData() as $key => $value ) {
            Request::set( $key, $value );
        }
        return $this;
    }

    public function copyFromRequest( ) {
        foreach ( $this->getData() as $key => $value ) {
            if ( ( $requestValue = Request::get( $key ) ) != '' ) {
                if ( $requestValue != $value ) {
                    $this->$key = $requestValue;
                }
            }
        }
        return $this;
    }
}
