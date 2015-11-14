<?php
namespace Jackbooted\Forms;

use \Jackbooted\Html\JS;
use \Jackbooted\Html\Lists;
use \Jackbooted\Html\Tag;
use \Jackbooted\Util\Invocation;
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

/**
 */
class Paginator extends Navigator {
    const STARTING_PAGE = 'G';
    const STARTING_ROW  = 'R';
    const TOTAL_ROWS    = 'T';
    const SQL_START     = 'Q';
    const ROWS_PER_PAGE = 'P';
    const LOG_THRESHOLD = 'L';
    const PAGE_VAR      = '_PG';
    const SUBMIT        = 'S';

    const PAGE_LINK_CLASS   = 'PAGE_LINK_CLASS';
    const PAGE_BUTTON_CLASS = 'PAGE_BUTTON_CLASS';

    /**
     * @var integer Counts the number of times that this class is invoked so
     * that each invokation can have a unique id
     */
    private static $pagination =  [ self::STARTING_ROW  => 0,
                                    self::STARTING_PAGE => 0,
                                    self::TOTAL_ROWS    => 0,
                                    self::SQL_START     => 0,
                                    self::ROWS_PER_PAGE => 10 ];

     private static $itemsPerPageList =  [ 5, 10, 20, 50, 100, 200 ];

    /**
     * @static
     * @param  $suffix
     * @return string
     */
    public static function navVar ( $suffix ) {
        return self::PAGE_VAR . $suffix;
    }

    private $dispPageSize;

    /**
     * Create a Pagination Object.
     * @param array $props This is the properties that the Paginator will use to display.
     * <pre>
     * $props = array ( 'attribs'          => 'array ( 'style' => 'display:none ), // Optional,
     *                                        // Attributes that will be stamped on the div that is generated
     *                                        // if not supplied will be empty array.
     *                                        // Need to supply if the primary key is not simple column name
     *                  'suffix'           => 'V', // Optional, suffix for the action variable for paginator
     *                                        // useful when there is a numbner on the screen
     *                                        // if not supplied one will be generated based on the number of
     *                                        // paginators that are generated
     *                  'request_vars'     => 'CEMID', // Optional, regexpression or individual name of any request
     *                                        //  vars that are to be copied to the response vars (chained vars)
     *                  'display_pagesize' => true, // Optional defaults to true. If false the page sizes will not
     *                                        // be displayed
     *                  'rows'             => 100,  // Optional. Number of rows that the Paginator has to deal with
     *                                        // Based on this number and the number of rows per page, the number of
     *                                        // pages are calculated
     *                );
     * </pre>
     */
    public function __construct ( $props=[] ) {
        $this->attribs      = ( isset ( $props['attribs'] ) ) ? $props['attribs'] :  [];
        $suffix             = ( isset ( $props['suffix'] ) )  ? $props['suffix']  : Invocation::next();
        $this->navVar       = self::navVar ( $suffix );
        $initPattern        = ( isset ( $props['request_vars'] ) ) ? $props['request_vars'] : '';
        $this->respVars     = new Response ( $initPattern );
        $this->dispPageSize = ( isset ( $props['display_pagesize'] ) ) ? $props['display_pagesize'] : true;

        // ensure that they have been set
        $requestPageVars = Request::get ( $this->navVar,  [] );
        foreach ( self::$pagination as $key => $val ) {
            $this->set ( $key, ( ( isset ( $requestPageVars[$key] ) ) ? $requestPageVars[$key] : $val ) );
        }

        if ( isset ( $props['rows'] ) ) $this->setRows ( (int)$props['rows'] );

        $this->styles[self::PAGE_LINK_CLASS]   = 'jb-pagelink';
        $this->styles[self::PAGE_BUTTON_CLASS] = 'jb-pagebuton';

        if ( $this->getStart () > 0 && $this->getRows () < $this->getPageSize () ) {
            $this->setStart ( 0 );
        }
    }
    /**
     * @param  $rows
     * @return Navigator
     */
    public function setRows ( $rows ) {
        return $this->set ( self::TOTAL_ROWS, $rows );
    }

    /**
     * @return Response
     */
    public function getRows ( ) {
        return $this->get ( self::TOTAL_ROWS );
    }

    /**
     * @return Response
     */
    public function getStart ( ) {
        return $this->get ( self::STARTING_ROW );
    }

    /**
     * @param  $start
     * @return Navigator
     */
    public function setStart ( $start ) {
        if ( $start > 0 && $this->getRows () < $this->getPageSize () ) {
            $start = 0;
        }

        return $this->set ( self::STARTING_ROW, $start );
    }

    /**
     * @return Response
     */
    public function getPageSize ( ) {
        return $this->get ( self::ROWS_PER_PAGE );
    }

    /**
     * @param  $val
     * @return Navigator
     */
    public function setPageSize ( $val ) {
        return $this->set ( self::ROWS_PER_PAGE, $val );
    }

    /**
     * @param  $key
     * @param  $value
     * @return Paginator
     */
    public function setStyle ( $key, $value ) {
        $this->styles[$key]   = $value;
        $this->formVars[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLimits( $dbType='MYSQL', $sql='' ) {
        $this->auditStartRow ();

        if ( $dbType == 'MYSQL' || $dbType == 'SQLITE' ) {
            return $sql . ' LIMIT ' . $this->getStart () . ',' . $this->getPageSize ();
        }
        else if ( $dbType == 'ORACLE' ) {
            $lowLim = $this->getStart ();
            $upLim  = $this->getStart () + $this->getPageSize ();
            if ( $sql == '' ) $sql = '%s';
            $qry = <<<SQL
                SELECT * FROM (
                    SELECT t__.*,
                           ROWNUM AS r__
                    FROM (
                        $sql
                    ) t__
                )
                WHERE r__ BETWEEN $lowLim AND $upLim-1
SQL;
            return $qry;
        }
        else {
            return '';
        }
    }

    public function auditStartRow () {
        if ( $this->getStart () >= $this->getRows () ) {
            $this->setStart ( 0 );
            $this->set ( self::STARTING_PAGE, 0 );
        }
        return $this;
    }

    /**
     * @return string
     */
    public function toHtml () {
        $this->auditStartRow ();

        $rowsPerPage      = intval ( $this->getPageSize () );
        $startingRow      = intval ( $this->getStart () );
        $startingPage     = intval ( $this->get ( self::STARTING_PAGE ) );
        $totalRows        = intval ( $this->getRows () );
        $saveStartingRow  = intval ( $this->getStart () );

        if ( $rowsPerPage <= 0 ) {
            $rowsPerPage = self::$pagination[self::ROWS_PER_PAGE];
        }

        // Not enough rows for pagination
        if ( $totalRows <= $rowsPerPage ) {
            if ( $this->dispPageSize && $totalRows > 10 ) {
                return Tag::div ( $this->attribs ) .
                         Tag::form (  [ 'method' => 'get' ] ) .
                           $this->toHidden (  [ self::ROWS_PER_PAGE ] ) .
                           '&nbsp;Max Rows:&nbsp;' .
                           Lists::select ( $this->toFormName ( self::ROWS_PER_PAGE ),
                                           self::$itemsPerPageList,
                                            [ 'default' => $rowsPerPage, 'onChange' => 'submit();' ] ) .
                         Tag::_form () .
                       Tag::_div ();
            }
            else {
                return '';
            }
        }

        if ( $startingPage > 0 ) {
            $startingRow = ( $startingPage - 1 ) *  $rowsPerPage;
            $this->set ( self::STARTING_PAGE, 0 );
        }

        if ( $startingRow >= $totalRows ) $startingRow = $totalRows - 1;

        $pageContainingStartRow = intval ( $startingRow / $rowsPerPage );
        $this->set ( self::SQL_START, $rowsPerPage * $pageContainingStartRow );

        // Get number of pages
        $numberOfPages = intval ( $totalRows / $rowsPerPage );
        if ( ( $totalRows % $rowsPerPage ) != 0 ) {
            $numberOfPages ++;
        }

        $previousPage = $nextPage = null;
        $html =  [  [],  [] ];
        $pLoc = 0;

        for ( $currentPage=$pageContainingStartRow+1,$incr=1; $currentPage<$numberOfPages-1; $currentPage+=$incr ) {
            $startingRowForThisPage = $currentPage * $rowsPerPage;
            $currentPageDisplay     = $currentPage + 1;
            $this->set ( self::STARTING_ROW, $startingRowForThisPage );
            $html[1][] = Tag::hRef ( $this->toUrl ( ), number_format ( $currentPageDisplay ),
                                      [ 'title' => 'Go to Page ' . $currentPageDisplay,
                                        'class' => $this->styles[self::PAGE_LINK_CLASS] ] );
            $incr *= count ( $html[1] );
        }

        if ( $pageContainingStartRow + 1 <  $numberOfPages ) {
            $this->setStart ( $rowsPerPage * ( $numberOfPages - 1 ) );
            $html[1][] = Tag::hRef ( $this->toUrl ( ), number_format ( $numberOfPages ),
                                      [ 'title' => 'Go to Page ' . $numberOfPages,
                                        'class' => $this->styles[self::PAGE_LINK_CLASS] ] );

            $this->setStart ( $rowsPerPage * ( $pageContainingStartRow + 1 ) );
            $url = $this->toUrl ( );
            $nextPage = Tag::button ( '>',  [ 'onclick' => "location.href='$url';return true;",
                                              'title' => 'Go to Next Page - ' . $pageContainingStartRow + 2,
                                              'class' => $this->styles[self::PAGE_BUTTON_CLASS] ] );
            $this->setStart ( $rowsPerPage * ( $numberOfPages - 1 ) );
            $url = $this->toUrl ( );
        }

        for ( $currentPage=$pageContainingStartRow-1,$incr=1; $currentPage>0; $currentPage-=$incr ) {
            $startingRowForThisPage = $currentPage * $rowsPerPage;
            $currentPageDisplay     = $currentPage + 1;
            $this->setStart ( $startingRowForThisPage );
            $html[0][] = Tag::hRef ( $this->toUrl ( ), number_format ( $currentPageDisplay ),
                                      [ 'title' => 'Go to Page ' . $currentPageDisplay,
                                        'class' => $this->styles[self::PAGE_LINK_CLASS] ] );
            $incr *= count ( $html[0] );
        }

        if ( $pageContainingStartRow != 0 ) {
            $this->setStart ( 0 );
            $html[0][] = Tag::hRef ( $this->toUrl ( ), 1,
                                      [ 'title' => 'Go to Page ' . 1,
                                        'class' => $this->styles[self::PAGE_LINK_CLASS] ] );

            $this->setStart ( $rowsPerPage * ( $pageContainingStartRow - 1 ) );
            $url = $this->toUrl (  );
            $previousPage = Tag::button ( '<',  [ 'onclick' => "location.href='$url';return true;",
                                                  'title' => 'Go to Previous Page - ' . $pageContainingStartRow - 2,
                                                  'class' => $this->styles[self::PAGE_BUTTON_CLASS] ] );
            $this->setStart ( 0 );
            $url = $this->toUrl ( );
        }
        $html[0] = array_reverse ( $html[0] );
        $this->setStart ( $saveStartingRow );
        $curPage = (string)($pageContainingStartRow + 1);

        $exemptVars =  [ self::STARTING_PAGE ];
        if ( $this->dispPageSize ) {
            $exemptVars[] = self::ROWS_PER_PAGE;
            $pageSizeHtml = '&nbsp;Max Rows:&nbsp;' .
                            Lists::select ( $this->toFormName ( self::ROWS_PER_PAGE ),
                                            self::$itemsPerPageList,
                                             [ 'default' => $rowsPerPage, 'onChange' => 'submit();' ] );
        }
        else {
            $pageSizeHtml = '';
        }

        return Tag::div ( $this->attribs ) .
                 Tag::form (  [ 'method' => 'get' ] ) .
                   $this->toHidden ( $exemptVars ) .
                   $previousPage .
                   '&nbsp;' . join ( '&nbsp;&#183;&nbsp;', $html[0] ) .
                   '&nbsp;' . Tag::text ( $this->toFormName ( self::STARTING_PAGE ),
                                           [ 'value' => $curPage,
                                             'size' => max ( 1, strlen ( $curPage ) - 1 ),
                                             'style' => 'font-weight:bold;' ] ) .
                   '&nbsp;' . join ( '&nbsp;&#183;&nbsp;', $html[1] ) .
                   '&nbsp;' . $nextPage .
                   $pageSizeHtml .
                 Tag::_form () .
               Tag::_div ();

    }
    public function toSlider () {
        $this->auditStartRow ();

        $rowsPerPage      = intval ( $this->getPageSize () );
        $startingRow      = intval ( $this->getStart () );
        $startingPage     = intval ( $this->get ( self::STARTING_PAGE ) );
        $totalRows        = intval ( $this->getRows () );
        $formId           = 'F' . self::PAGE_VAR . '_' . $divTag;
        $saveStartingRow  = intval ( $this->getStart () );

        if ( $rowsPerPage <= 0 ) {
            $rowsPerPage = self::$pagination[self::ROWS_PER_PAGE];
        }

        // Not enough rows for pagination
        if ( $totalRows <= $rowsPerPage ) {
            return Tag::div ( $this->attribs ) .
                     Tag::form (  [ 'method' => 'get' ] ) .
                       $this->toHidden (  [ self::ROWS_PER_PAGE ] ) .
                       'Display:&nbsp;' .
                       Lists::select ( $this->toFormName ( self::ROWS_PER_PAGE ),
                                       self::$itemsPerPageList,
                                        [ 'default' => $rowsPerPage, 'onChange' => 'submit();' ] ) .
                     Tag::_form () .
                   Tag::_div ();
        }

        if ( $startingPage > 0 ) {
            $startingRow = ( $startingPage - 1 ) *  $rowsPerPage;
            $this->set ( self::STARTING_PAGE, 0 );
        }

        if ( $startingRow >= $totalRows ) $startingRow = $totalRows - 1;

        $pageContainingStartRow = intval ( $startingRow / $rowsPerPage );
        $this->set ( self::SQL_START, $rowsPerPage * $pageContainingStartRow );

        // Get number of pages
        $numberOfPages = intval ( $totalRows / $rowsPerPage );
        if ( ( $totalRows % $rowsPerPage ) != 0 ) {
            $numberOfPages ++;
        }

        $js = <<<JS
        $().ready ( function () {
            $('#slider{$divTag}').slider ({
                value: $startingPage,
                min: 1,
                max: $numberOfPages,
                slide: function ( event, ui ) {
                    $('#slider-page{$divTag}').val( ui.value );
                },
                change: function ( event, ui ) {
                    $('#slider-page{$divTag}').val( ui.value );
                    $('#{$formId}').submit ();
                }
            });
            $('#slider-page{$divTag}').val( $( '#slider{$divTag}' ).slider( 'value' ) );
            $('#slider-left{$divTag}').button()
                                      .click ( function () {
                                           var val = $( '#slider{$divTag}' ).slider( 'value' );
                                           $( '#slider{$divTag}' ).slider( 'value', val - 1 );
                                       });
            $('#slider-right{$divTag}').button()
                                       .click ( function () {
                                            var val = $( '#slider{$divTag}' ).slider( 'value' );
                                            $( '#slider{$divTag}' ).slider( 'value', val + 1 );
                                        });
        });
JS;

        $formHtml = Tag::form (  [ 'method' => 'get', 'id' => $formId ] ) .
                      $this->toHidden (  [ self::STARTING_PAGE, self::ROWS_PER_PAGE ] ) .
                      Tag::text ( $this->toFormName ( self::STARTING_PAGE ),
                                   [ 'value' => $startingPage,
                                     'size'  => max ( 1, strlen ( $startingPage ) - 1 ),
                                     'id'    => 'slider-page' . $divTag,
                                     'style' => 'font-weight:bold;' ] ) .
                      Lists::select ( $this->toFormName ( self::ROWS_PER_PAGE ),
                                      self::$itemsPerPageList,
                                      [ 'default' => $rowsPerPage, 'onChange' => 'submit();' ] ) .
                 Tag::_form ();

        $html = <<<HTML
    <table>
        <tr>
            <td width="10" align="center" valign="middle">
                <a href="javascript:void(0)" id="slider-left{$divTag}" style="font-size: 0.7em;">&lt;</a>
            </td>
            <td width="500"  align="center" valign="middle">
                <div id="slider{$divTag}"></div>
            </td>
            <td width="10" align="center" valign="middle">
                <a href="javascript:void(0)" id="slider-right{$divTag}" style="font-size: 0.7em;">&gt;</a>
            </td>
            <td align="center" valign="middle">
                $formHtml
            </td>
        </tr>
    </table>
HTML;

        return JS::libraryWithDependancies ( JS::JQUERY_UI ) .
               JS::javaScript ( $js ) .
               $html;
    }
}