<?php

namespace Jackbooted\Forms;

use \Jackbooted\Html\Tag;
use \Jackbooted\Util\Invocation;

/**
 * @copyright Confidential and copyright (c) 2020 Jackbooted Software. All rights reserved.
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
class Columnator extends Navigator {

    const SORT_COL = 'C';
    const SORT_ORDER = 'O';
    const COL_VAR = '_CL';
    const COL_LINK_CLASS = 'COL_LINK_CLASS';
    const COL_BUTTON_CLASS = 'COL_BUTTON_CLASS';

    private static $columnation = [
        self::SORT_COL => '',
        self::SORT_ORDER => '' ];

    /**
     * @static
     * @param  $suffix
     * @return string
     */
    public static function navVar( $suffix ) {
        return self::COL_VAR . $suffix;
    }

    private $sortColumn;
    private $sortOrder;

    /**
     * Create a Columnator Object.
     * @param array $props This is the properties that the Columnator will use to display.
     * <pre>
     * $props = array ( 'attribs'          => 'array ( 'style' => 'display:none ), // Optional,
     *                                        // Attributes that will be stamped on the div that is generated
     *                                        // if not supplied will be empty array.
     *                                        // Need to supply if the primary key is not simple column name
     *                  'suffix'           => 'V', // Optional, suffix for the action variable for Columnator
     *                                        // useful when there is a numbner on the screen
     *                                        // if not supplied one will be generated based on the number of
     *                                        // Columnator that are generated
     *                  'request_vars'     => 'CEMID', // Optional, regexpression or individual name of any request
     *                                        //  vars that are to be copied to the response vars (chained vars)
     *                  'init_column'      => 'fldDate', // Optional, Initial Coloumn to be sorted
     *                  'init_order'       => 'DESC', // Optional, initial direction
     *                );
     * </pre>
     */
    public function __construct( $props = [] ) {
        parent::__construct();

        $this->attribs = ( isset( $props['attribs'] ) ) ? $props['attribs'] : [];
        $suffix = ( isset( $props['suffix'] ) ) ? $props['suffix'] : Invocation::next();
        $this->navVar = self::navVar( $suffix );
        $initPattern = ( isset( $props['request_vars'] ) ) ? $props['request_vars'] : '';
        $this->respVars = new Response( $initPattern );

        $initialVars = self::$columnation;
        $initialVars[self::SORT_COL] = ( isset( $props['init_column'] ) ) ? $props['init_column'] : '';
        $initialVars[self::SORT_ORDER] = ( isset( $props['init_order'] ) ) ? $props['init_order'] : '';

        // ensyre that they have been set
        $requestColumnVars = Request::get( $this->navVar, [] );
        foreach ( $initialVars as $key => $val ) {
            $this->set( $key, ( ( isset( $requestColumnVars[$key] ) ) ? $requestColumnVars[$key] : $val ) );
        }

        // Get the current settings
        $this->sortColumn = $this->formVars[self::SORT_COL];
        $this->sortOrder = $this->formVars[self::SORT_ORDER];
        if ( !isset( $this->sortOrder ) || $this->sortOrder == false || !in_array( $this->sortOrder, [ 'ASC', 'DESC' ] ) ) {
            $this->sortOrder = 'ASC';
        }

        $this->styles[self::COL_LINK_CLASS] = 'jb-collink';
        $this->styles[self::COL_BUTTON_CLASS] = 'jb-colbutton';
    }

    /**
     * @param  $columnName
     * @param  $columnDisplay
     * @return string
     */
    public function toHtml( $columnName, $columnDisplay = null ) {
        if ( $columnDisplay == null )
            $columnDisplay = $columnName;
        $savedFormVars = $this->formVars;

        $this->set( self::SORT_COL, $columnName );

        if ( $this->sortColumn == $columnName ) {
            $this->set( self::SORT_ORDER, ( $this->sortOrder == 'ASC' ) ? 'DESC' : 'ASC'  );
            $sortDirectionName = ( $this->sortOrder == 'ASC' ) ? 'Decending' : 'Ascending';
            $title = 'Click here to sort ' . $sortDirectionName . ' By ' . $columnDisplay;

            $button = ( $this->sortOrder == 'ASC' ) ? '^' : 'v';

            $url = $this->toUrl();
            $html = Tag::hTag( 'a', array_merge( $this->attribs, [ 'href' => $url,
                        'class' => $this->styles[self::COL_LINK_CLASS],
                        'title' => $title ] ) ) .
                    $columnDisplay .
                    Tag::_hTag( 'a' ) .
                    Tag::linkButton( $url, $button, array_merge( $this->attribs, [ 'class' => $this->styles[self::COL_BUTTON_CLASS],
                        'title' => $title ] ) );
        }
        else {
            $this->set( self::SORT_ORDER, $this->sortOrder );
            $sortDirectionName = ( $this->sortOrder == 'ASC' ) ? 'Decending' : 'Ascending';
            $title = 'Sort ' . $sortDirectionName . ' By ' . $columnDisplay;

            $url = $this->toUrl();
            $html = Tag::hTag( 'a', array_merge( $this->attribs, [ 'href' => $url,
                        'class' => $this->styles[self::COL_LINK_CLASS],
                        'title' => $title ] ) ) .
                    $columnDisplay .
                    Tag::_hTag( 'a' );
        }

        $this->formVars = $savedFormVars;
        return $html;
    }

    /**
     * @return string
     */
    public function getSort() {
        if ( !isset( $this->formVars[self::SORT_COL] ) || $this->formVars[self::SORT_COL] == false ) {
            return '';
        }

        return ' ORDER BY ' . $this->formVars[self::SORT_COL] . ' ' . $this->formVars[self::SORT_ORDER];
    }

}
