<?php

namespace Jackbooted\DB;

use \Jackbooted\DB\DB;
use \Jackbooted\Forms\Request;
use \Jackbooted\Forms\Response;
use \Jackbooted\Html\Lists;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Html\Widget;
use \Jackbooted\Util\Invocation;
use \Jackbooted\Util\Log4PHP;

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
 *
 */
class DBEdit extends \Jackbooted\Util\JB {

    const TABLE_C   = 'TABLE_CLASS';
    const SUFFIX    = '_DE';
    const ACTION    = '_DEA';
    const DISPLAY   = 'DISPLAY';
    const HIDDEN    = 'HIDDEN';
    const NONE      = 'NONE';
    const SELECT    = 'SELECT';
    const RADIO     = 'RADIO';
    const ENCTEXT   = 'ENCTEXT';
    const TEXT      = 'TEXT';
    const TEXTAREA  = 'TEXTAREA';
    const TINYMCE   = 'TINYMCE';
    const CHECKBOX  = 'CHECKBOX';
    const TIMESTAMP = 'TIMESTAMP';

    private $db;
    private $dbType;
    private $displayRows;
    private $suffix;
    private $formAction;
    private $canUpdate;
    private $canDelete;
    private $canInsert;
    private $insDefaults;

    private $displayType = [];
    private $cellAttributes = [];
    private $columnTitles = [];

    private $log;
    private $resp;
    private $ormClass;
    private $daoObject;
    private $selectSQL;
    private $defaultID;

    public static function factory( $ormClass, $selectSQL, $extraArgs = [] ) {
        return new DBEdit( $ormClass, $selectSQL, $extraArgs );
    }

    /**
     * Create the DBEdit Object.
     * @param string $tableName The name of the table
     * @param array $extraArgs This is the properties that the DBEdit will use to display/populate the database.
     * <pre>
     * </pre>
     */
    public function __construct( $ormClass, $selectSQL, $extraArgs = [] ) {
        parent::__construct();
        $this->log  = Log4PHP::logFactory( __CLASS__ );
        $this->resp = new Response ();

        $this->db          = ( isset( $extraArgs['db'] ) )          ? $extraArgs['db']          : DB::DEF;
        $this->dbType      = ( isset( $extraArgs['dbType'] ) )      ? $extraArgs['dbType']      : DB::driver( $this->db );
        $this->displayRows = ( isset( $extraArgs['displayRows'] ) ) ? $extraArgs['displayRows'] : 10;
        $this->suffix      = ( isset( $extraArgs['suffix'] ) )      ? $extraArgs['suffix']      : '_' . Invocation::next();
        $this->formAction  = ( isset( $extraArgs['formAction'] ) )  ? $extraArgs['formAction']  : '?';
        $this->insDefaults = ( isset( $extraArgs['insDefaults'] ) ) ? $extraArgs['insDefaults'] : [];
        $this->canDelete   = ( isset( $extraArgs['canDelete'] ) )   ? $extraArgs['canDelete']   : true;
        $this->canUpdate   = ( isset( $extraArgs['canUpdate'] ) )   ? $extraArgs['canUpdate']   : true;
        $this->canInsert   = ( isset( $extraArgs['canInsert'] ) )   ? $extraArgs['canInsert']   : true;

        $this->ormClass    = $ormClass;
        $daoClass          = $ormClass . 'DAO';
        $this->daoObject   = new $daoClass();
        $this->selectSQL   = $selectSQL;
        $this->action      = self::ACTION . $this->suffix;
        $this->submitId    = 'S' . $this->suffix;
        $this->defaultID   = $this->getDefaultID();

        $this->setupDefaultStyle();

        $this->copyVarsFromRequest( WebPage::ACTION );
    }

    public function index() {

        $html = $this->controller();

        if ( ( $id = Request::get( $this->daoObject->primaryKey, $this->defaultID ) ) == '' ) {
            return 'No Default ID';
        }

        $html .='<H4>Click on row to edit this item</h4>' .
                Tag::table() .
                  Tag::tr() .
                    Tag::td( ['valign' => 'top'] ) .
                      Tag::form( [ 'method' => 'get' ] ) .
                        $this->resp->set( $this->action, 'dummyClick' )->toHidden ( false ) .
                        Lists::select ( $this->daoObject->primaryKey,
                                        $this->selectSQL,
                                        [ 'size' => $this->displayRows,'onClick' => 'submit();', 'default' => $id ] ) .
                      Tag::_form () .
                    Tag::_td() .
                    Tag::td( [ 'widdth' => '100%' ] ) .
                      $this->indexItem( $id ) .
                    Tag::_td() .
                  Tag::_tr() .
                  Tag::tr() .
                    Tag::td( ['colspan' => '10'] ) .
                      Tag::linkButton( $this->formAction . $this->resp->set( $this->action, 'insertBlank' )->toUrl(), 'Insert Blank' ) .
                    Tag::_td() .
                  Tag::_tr() .
                Tag::_table();

        return $html;
    }

    public function dummyClick() {
        return '';
    }

    public function insertBlank() {
        $ormClass = $this->ormClass;
        $ormObject = $ormClass::create( $this->insDefaults );
        Request::set( $this->daoObject->primaryKey, $ormObject->id );
        return Widget::popupWrapper( "Inserted one object ID:{$ormObject->id}" );
    }

    private function indexItem( $id ) {
        $ormClass = $this->ormClass;
        $ormObject = $ormClass::load( $id )->copyToRequest();
        foreach ( $ormObject->getData() as $col => $val ) {
            $this->columnTitles[$col]   = $this->convertColumnToTitle( $col );
            $this->cellAttributes[$col] = [];
            $this->calculateColumnWidth( $col, $val );
        }

        $resp = $this->resp->set( $this->daoObject->primaryKey, $id );

        $html = Tag::form() .
                  $resp->set( $this->action, 'save' )->toHidden( ) .
                  Tag::table();

        foreach ( $ormObject->getData() as $key => $value ) {
            $html .= $this->renderValue( $key, $value );
        }

        $html .=    Tag::tr() .
                      Tag::td([ 'colspan' => 10]) .
                        Tag::submit( 'Save' ) .
                        Tag::linkButton( $this->formAction . $this->resp->set( $this->action, 'dup' )->toUrl(), 'Dup' ) .
                        Tag::linkButton( $this->formAction . $this->resp->set( $this->action, 'del' )->toUrl(), 'Del' ) .
                      Tag::_td() .
                    Tag::_tr() .
                  Tag::_table() .
                Tag::_form ();

        return $html;
    }

    private function renderValue( $colName, $value ) {
        $html = '';
        $tinyMCEJS = '';

        $type = $this->getColumnType( $colName );
        $updCheckAttrib = [];

        switch ( $type ) {
            case self::NONE:
                break;

            case self::DISPLAY:
                $html .= ( $value == '' ) ? '&nbsp;' : Tag::e( $value );
                break;

            case self::HIDDEN:
                $this->resp->set( $colName, $value );
                break;

            case self::RADIO:
                $dispList = ( isset( $this->displayType[$colName][1] ) ) ? $this->displayType[$colName][1] : null;
                $updCheckAttrib['default'] = $value;
                $html .= Tag::table() .
                           Tag::tr() .
                             Tag::td( [ 'nowrap' => 'nowrap' ] ) .
                               implode( Tag::_td() . Tag::td( [ 'nowrap' => 'nowrap' ] ), Lists::radio( $colName, $dispList, $updCheckAttrib ) ) .
                             Tag::_td() .
                           Tag::_tr() .
                         Tag::_table();
                break;

            case self::SELECT:
                $dispList  = ( isset( $this->displayType[$colName][1] ) ) ? $this->displayType[$colName][1] : null;
                $blankLine = ( isset( $this->displayType[$colName][2] ) ) ? $this->displayType[$colName][2] : false;
                $updCheckAttrib['default'] = $value;
                $updCheckAttrib['hasBlank'] = $blankLine;
                $html .= Lists::select( $colName, $dispList, $updCheckAttrib );
                break;

            case self::CHECKBOX:
                $checkValue = ( isset( $this->displayType[$colName][1] ) ) ? $this->displayType[$colName][1] : 'YES';
                $html .= Tag::checkBox( $colName, $checkValue, $value == $checkValue );
                break;

            case self::TIMESTAMP:
                $attribs = array_merge( $updCheckAttrib, $this->cellAttributes[$colName] );
                $attribs['value'] = strftime( '%Y-%m-%d %H:%M:%S', (int) $value );
                $attribs['size'] = strlen( $attribs['value'] ) + 1;
                $html .= Tag::text( $colName, $attribs );
                break;

            case self::TEXTAREA:
                $attribs = array_merge( [ 'rows' => 5, 'cols' => 40 ], $updCheckAttrib, $this->cellAttributes[$colName] );
                $html .= Tag::textArea( $colName, $value, $attribs );
                break;

            case self::TINYMCE:
                if ( $tinyMCEJS == '' ) {
                    $tinyMCEJS = Widget::tinyMCE( '.dbedit_tinymce' );
                }

                $attribs = array_merge( [ 'rows' => '13',
                                          'style' => 'width:100%;',
                                          'class' => 'dbedit_tinymce',
                                          'title' => 'Edit this field' ], $updCheckAttrib, $this->cellAttributes[$colName] );
                $html .= Tag::textArea ( $colName, $value, $attribs );
                break;

            case self::ENCTEXT:
                $value = Cryptography::de( (string) $value );
                // Fall through to output text field

            case self::TEXT:
            default:
                $updCheckAttrib['value'] = (string) $value;
                $html .= Tag::text( $colName, array_merge( $updCheckAttrib, $this->cellAttributes[$colName] ) );
                break;
        }

        if ( !in_array( $type, [ self::HIDDEN, self::NONE ] ) ) {
            $html = Tag::tr() .
                      Tag::th( [ 'valign' => 'top' ] ) . $this->convertColumnToTitle( $colName ) . Tag::_th() .
                      Tag::td() . $html .Tag::_td() .
                    Tag::_tr();
        }

        return $tinyMCEJS .
               $html;
    }

    private function getColumnType( $colName ) {
        if ( isset( $this->displayType[$colName] ) ) {
            if ( is_string( $this->displayType[$colName] ) ) {
                $type = $this->displayType[$colName];
            }
            else {
                $type = $this->displayType[$colName][0];
            }
        }
        else if ( $colName == $this->daoObject->primaryKey ) {
            $type = self::DISPLAY;
        }
        else {
            $type = self::TEXT;
        }

        if ( ! $this->canUpdate && ! in_array( $type, [ self::HIDDEN, self::NONE ] ) ) {
            $type = self::DISPLAY;
        }
        return $type;
    }

    public function dup( ) {
        if ( ( $id = Request::get( $this->daoObject->primaryKey ) ) == '' ) {
            return Widget::popupWrapper( 'Error. Invalid Object ID' );
        }

        $ormClass = $this->ormClass;
        $ormObject = $ormClass::create( $ormClass::load( $id )->getData() );
        Request::set( $this->daoObject->primaryKey, $ormObject->id );
        return Widget::popupWrapper( "Created duplicate row {$ormObject->id}" );
    }

    public function del( ) {
        if ( ( $id = Request::get( $this->daoObject->primaryKey ) ) == '' ) {
            return Widget::popupWrapper( 'Error. Invalid Object ID' );
        }

        $ormClass = $this->ormClass;
        $ormObject = $ormClass::load( $id );
        $ormObject->delete();
        Request::set( $this->daoObject->primaryKey, $this->getDefaultID() );
        return Widget::popupWrapper( "Deleted row {$ormObject->id}" );
    }
    public function save( ) {
        if ( ( $id = Request::get( $this->daoObject->primaryKey ) ) == '' ) {
            return Widget::popupWrapper( 'Error. Invalid Object ID' );
        }

        $ormClass = $this->ormClass;
        $ormClass::load( $id )->copyFromRequest()->save();

        return Widget::popupWrapper( 'Saved Item ' . $id );
    }


    public function copyVarsFromRequest( $v ) {
        $this->resp->copyVarsFromRequest( $v );
        return $this;
    }

    /**
     * Sets up custom display for columns
     * @param string $colName
     * @param mixed $colStyle
     * @return DBEdit current instance for chaining
     * <pre>
     * $crud->setColDisplay ( 'fldUserID',      array ( DBEdit::SELECT, 'SELECT id,username FROM tblUser', $displayBlank ) )
     * $crud->setColDisplay ( 'fldGroupID',     array ( DBEdit::SELECT, self::GROUP_SQL, true ) )
     * $crud->setColDisplay ( 'fldLevelID',     array ( DBEdit::SELECT, array ( 1, 2, 3 ), true ) )
     * $crud->setColDisplay ( 'fldPrivilegeID',  DBEdit::DISPLAY )
     * </./pre>
     */
    public function setColDisplay( $colName, $colStyle ) {
        $this->displayType[$colName] = $colStyle;
        return $this;
    }

    public function setProperty( $name, $value ) {
        $this->$name = $value;
        return $this;
    }

    public function getProperty( $name ) {
        return $this->$name;
    }

    public function columnAttrib( $col, $attrib = [] ) {
        foreach ( $attrib as $key => $val ) {
            $this->cellAttributes[$col][$key] = $val;
        }
        return $this;
    }

    public function style( $type, $attribs = null ) {
        if ( $attribs === null ) {
            unset( $this->styles[$type] );
        }
        else {
            $this->styles[$type] = $attribs;
        }
        return $this;
    }

    private function setupDefaultStyle() {
        $this->styles[self::TABLE_C] = [ 'cellpadding' => 1, 'cellspacing' => 0, 'border' => 1 ];
    }

    private function convertColumnToTitle( $col ) {
        if ( $col == $this->daoObject->primaryKey )
            return 'ID';

        $title = '';

        if ( substr( $col, 0, 3 ) == 'fld' ) {
            $title = self::jbCol2Title( $col );
        }
        else if ( substr( $col, 0, 2 ) == 'f_' ) {
            foreach ( explode( '_', substr( $col, 2 ) ) as $segment ) {
                if ( $title != '' )
                    $title .= ' ';
                $title .= ucfirst( $segment );
            }
        }
        else {
            foreach ( explode( '_', $col ) as $segment ) {
                if ( $title != '' )
                    $title .= ' ';
                $title .= ucfirst( $segment );
            }
        }
        return $title;
    }

    private static function jbCol2Title( $colP ) {
        $col = substr( $colP, 3 );
        $title = '';
        $colLen = strlen( $col );
        $lastCharacterIsUpper = true;
        for ( $i = 0; $i < $colLen; $i++ ) {
            $ch = substr( $col, $i, 1 );
            $curCharacterIsUpper = ctype_upper( $ch );
            if ( $curCharacterIsUpper && !$lastCharacterIsUpper )
                $title .= ' ';
            $lastCharacterIsUpper = $curCharacterIsUpper;
            $title .= $ch;
        }
        return $title;
    }
    private function controller() {
        if ( ( $action = Request::get( $this->action ) ) == '' ) {
            return '';
        }
        Request::set( $this->action, ''  );
        if ( ! method_exists( $this, $action ) ) {
            return "Method: $action does not exist";
        }

        return $this->$action();
    }
    private function getDefaultID() {
        foreach ( DBTable::factory( $this->db, $this->selectSQL ) as $row ) {
            return $row[0];
        }
        return false;
    }
    private function calculateColumnWidth( $colName, $value ) {
        if ( isset( $this->cellAttributes[$colName]['size'] ) ) {
            return;
        }

        $width = strlen( $value );
        if ( $width > 40 ) {
            $width = 40;
        }

        if ( $width >= 0 && $width <= 40 ) {
            $this->cellAttributes[$colName]['size'] = $width;
        }
    }
}
