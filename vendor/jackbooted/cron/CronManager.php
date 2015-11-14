<?php
namespace Jackbooted\Cron;

use \Jackbooted\Forms\CRUD;
use \Jackbooted\Html\WebPage;
/**
 * @copyright Confidential and copyright (c) 2015 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */

class CronManager extends WebPage  {

    /**
     * @return string
     */
    public function index () {
        $dao = new CronDAO ();
        $cols = array_flip ( $dao->objToRel (  [ 'command' => 0, 'priority' => 1, 'result' => 2 ] ) );

        $crud = new CRUD ( $dao->tableName );
        $crud->setColDisplay ( $cols[0], CRUD::DISPLAY );
        $crud->setColDisplay ( $cols[1],  [ CRUD::SELECT,  [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ] ] );
        $crud->setColDisplay ( $cols[2], CRUD::DISPLAY );
        return $crud->index ();
    }
}
