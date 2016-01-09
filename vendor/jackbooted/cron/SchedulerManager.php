<?php
namespace Jackbooted\Cron;

use \Jackbooted\Forms\Request;
use \Jackbooted\Forms\Response;
use \Jackbooted\Html\JS;
use \Jackbooted\Html\Lists;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\Validator;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Html\Widget;
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

class SchedulerManager extends WebPage  {

    /**
     * @return string
     */
    public static function hello () {
        return  [ 0, 'Hello World' ];
    }

    public function index () {
        $schedulerList = Scheduler::getList ( true );
        $formName = 'SchedulerManager_index';
        $id = 'SchedulerManager_table';

        $js = "$().ready ( function () {\n";

        $valid = Validator::factory ( $formName );

        $html = Tag::table ( [ 'id' => $id ] ) .
                  Tag::tr () .
                    Tag::td () . 'Upd' .       Tag::_td () .
                    Tag::td () . 'Del' .       Tag::_td () .
                    Tag::th () . 'Command' .   Tag::_th () .
                    Tag::th () . 'Start Date'. Tag::_th () .
                    Tag::th () . 'Cron'  .     Tag::_th () .
                    Tag::th () . 'Active'    . Tag::_th () .
                    Tag::th () . 'Last Run'  . Tag::_th () .
                  Tag::_tr ();

        if ( count ( $schedulerList ) == 0 ) {
            $html .= Tag::tr () .
                       Tag::td (  [ 'colspan' => 20 ] )  .
                         'No Scheduled Tasks' .
                       Tag::_td () .
                     Tag::_tr ();
        }
        else {
            $js .= <<<JS
                $('input[type=checkbox][name^=fldUpd]').shiftClick();

JS;
            $rowIdx = 0;
            foreach ( $schedulerList as $idx => $schedulerItem ) {
                $row = '_' . $idx;

                $valid->addExists ( 'fldCommand' . $row, 'Command must exist' )
                      ->addExists ( 'fldCron' . $row, 'Interval must exist' );

                $js .= <<<JS
                    $( '#fldStartDate$row' ).datetimepicker({
                        dateFormat: 'yy-mm-dd',
                        timeFormat: 'HH:mm'
                    });
JS;
                $lastRun = $schedulerItem->lastRun;
                if ( ! isset( $lastRun ) || $lastRun == false ) {
                    $lastRun = '*Never*';
                }

                $html .= Tag::tr () .
                           Tag::td () .
                             Tag::checkBox( 'fldUpd[]', $idx, false,  [ 'id' => 'U' . $rowIdx ] ) .
                           Tag::_td () .
                           Tag::td () .
                             Tag::linkButton ( '?' . Response::factory()
                                                             ->set ( 'fldID', $idx )
                                                             ->action ( __CLASS__ . '->deleteItem()' ),
                                               'Delete',
                                                [ 'onClick' => "confirm('Are you sure?')" ] ) .
                           Tag::_td () .
                           Tag::td ( ['width' => '100%', 'nowrap' => 'nowrap' ]) .
                             Tag::text ( 'fldCommand' . $row,   $schedulerItem->cmd,
                                          [ 'style' => 'width:100%;',
                                            'onChange' => "$('#U$rowIdx').attr('checked',true)" ] ) .
                           Tag::_td () .
                           Tag::td ( ['nowrap' => 'nowrap' ] ) .
                             Tag::text ( 'fldStartDate' . $row, $schedulerItem->start,
                                          [ 'id' => 'fldStartDate' . $row,
                                            'size' => '18',
                                            'onChange' => "$('#U$rowIdx').attr('checked',true)" ] ) .
                           Tag::_td () .
                           Tag::td ( ['nowrap' => 'nowrap' ] ) .
                             Tag::text ( 'fldCron' . $row,  $schedulerItem->cron,
                                          [ 'onChange' => "$('#U$rowIdx').attr('checked',true)" ]  ) .
                           Tag::_td () .
                           Tag::td ( ['nowrap' => 'nowrap' ] ) .
                             Lists::select ( 'fldActive' . $row,  [ 'Yes', 'No' ],
                                               [ 'default' => $schedulerItem->active,
                                                 'onChange' => "$('#U$rowIdx').attr('checked',true)" ] ) .
                           Tag::_td () .
                           Tag::td ( ['nowrap' => 'nowrap' ] ) . $lastRun . Tag::_td () .
                         Tag::_tr ();
                $rowIdx ++;
            }
        }

        $html .= Tag::_table ();
        $js .= '});';

        return JS::libraryWithDependancies( JS::JQUERY_UI_DATETIME ) .
               JS::library ( 'jquery.shiftclick.js' ) .
               JS::javaScript( $js ) .
               $valid->toHtml() .
               Widget::styleTable ( '#' . $id ) .
               Tag::form (  [ 'name' => $formName, 'onSubmit' => $valid->onSubmit() ] ) .
                 $html .
                 Response::factory()->action ( __CLASS__ . '->save()' )->toHidden() .
                 Tag::submit ( 'Save' ) .
                 Tag::linkButton ( '?' . Response::factory()->action ( __CLASS__ . '->newItem()' ), 'New Item' ) .
               Tag::_form ();
    }

    public function save () {
        foreach ( Request::get ( 'fldUpd',  [] ) as $id ) {
            $data =  [ 'id' => $id,
                       'cmd' => Request::get ( 'fldCommand_' . $id ),
                       'start' => Request::get ( 'fldStartDate_' . $id ),
                       'cron' => Request::get ( 'fldCron_' . $id ),
                       'active' => Request::get ( 'fldActive_' . $id ) ];
            Scheduler::factory ( $data )->save ();
        }

        return Widget::popupWrapper ( 'Saved Item(s)' ) .
               $this->index ();
    }
    public function deleteItem () {
        $id = Request::get ( 'fldID' );
        Scheduler::factory (  [ 'id' => $id ] )->delete ();
        return Widget::popupWrapper ( 'Deleted Item: ' . $id ) .
               $this->index ();
    }

    public function newItem () {
        $defaults =  [ 'cmd'      => __CLASS__ . '::hello();',
                       'start'    => date ( 'Y-m-d H:i' ),
                       'cron'     => '* * * * *',
                       'active'   => 'No' ];
        Scheduler::factory ( $defaults )->save ();
        return Widget::popupWrapper ( 'New Item Created' ) .
               $this->index ();
    }
}
