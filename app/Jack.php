<?php

namespace App;

use \Jackbooted\Config\Cfg;
use \Jackbooted\Forms\CRUD;

class Jack extends \Jackbooted\Util\Module {

    public static function crud( CRUD &$crud ) {
        switch ( $crud->getTableName() ) {
            case 'tblModJackAlert':
                $crud->setColDisplay( 'fldType', [ CRUD::SELECT, \App\Models\Alerts::$typeList ] );
                $crud->setColDisplay( 'fldStatus', [ CRUD::SELECT, \App\Models\Alerts::$statusList ] );
                break;
        }
    }

    public static function getBackground() {
        return Cfg::get( 'images_url' ) . '/spacer.gif';
    }

    public static function logo() {
        return Cfg::get( 'images_url' ) . '/JackLogo.gif';
    }

}
