<?php
namespace App\Controllers;

use \Jackbooted\DB\DB;
use \Jackbooted\DB\DBTable;
use \Jackbooted\DB\DBEdit;
use \Jackbooted\Forms\Request;
use \Jackbooted\Forms\CRUD;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Html\Widget;
use \Jackbooted\Util\MenuUtils;
use \Jackbooted\Html\Lists;
use \Jackbooted\Util\AutoLoader;
use \Jackbooted\Util\ClassLocator;

use \App\Models\Alerts;

class JackMain extends WebPage {
    const DEF = '\App\Controllers\JackMain->index()';

    public static function menu ( $resp=null ) {
        if ( $resp == null ) $resp = MenuUtils::responseObject ();

        return [ [ 'name'    => 'Home',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->index()' ),
                   'attribs' =>  [ 'title' => 'Home Page' ] ],
                 [ 'name'    => 'Recent Alerts',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->recentAlarms()' ),
                   'attribs' =>  [ 'title' => 'List of recent Alerts' ] ],
                 [ 'name'    => 'Edit Alerts',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->editAlerts()' ),
                   'attribs' =>  [ 'title' => 'Edit a list recent Alerts' ] ],
                 [ 'name'    => 'CRUD Alerts',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->crudAlerts()' ),
                   'attribs' =>  [ 'title' => 'Edit a list recent Alerts' ] ],
                 [ 'name'    => 'TODO List',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->todo()' ),
                   'attribs' =>  [ 'title' => 'List of outstanding items' ] ],
                 [ 'name'    => 'Browse Classes',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->browse()' ),
                   'slug'    => 'browse_classes',
                   'attribs' => [ 'title' => 'Browse All Classes' ] ],
                 [ 'name'    => 'Edit Account',
                   'url'     => '?' . $resp->action ( '\Jackbooted\Admin\Admin->editAccount()' ),
                   'attribs' =>  [ 'title' => 'Edit My Account Details' ] ],
                 [ 'name'    => 'Logout',
                   'url'     => 'ajax.php?' . $resp->action ( '\Jackbooted\Admin\Login::logout()' ),
                   'attribs' =>  [ 'title' => 'Logout' ] ],
                ];
    }

    public function index () {
        $html = <<<HTML
            So if you are reading this then you have just installed Jackbooted framework. Yes I knmow why do you want another
            framework. Well I have been working on this one since 2000 and it has grown and evolved since then. It is very
            easy to create efficient web sites with just a few clicks.
HTML;
        return '<h2>Welcome to Jackbooted PHP Framework</h2>' .
                $html;
    }

    public function editAlerts() {
        error_reporting( -1 );
        ini_set('display_errors', '1');

        $editTable = new DBEdit( '\App\Models\Alerts',
                                 'SELECT fldModJackAlertID,fldDescription FROM tblModJackAlert' );

        $editTable->copyVarsFromRequest( MenuUtils::ACTIVE_MENU );

        $html = $editTable->index( 'AL0000005' );

        return $html;
    }

    public function crudAlerts() {
        error_reporting( -1 );
        ini_set('display_errors', '1');
        $crud = new CRUD( 'tblModJackAlert',  [ 'insDefaults' =>  [ 'fldType'        => Alerts::TYPE_DEBUG,
                                                                    'fldStatus'      => Alerts::STATUS_NEW,
                                                                    'fldErrorID'     => 'JB001',
                                                                    'fldProcess'     => __FUNCTION__,
                                                                    'fldDescription' => __METHOD__ ],
                                                'nullsEmpty'  => true ] );

        $crud->setColDisplay ( 'fldStatus', Alerts::$statusList );
        $crud->setColDisplay ( 'fldType',   Alerts::$typeList );
        $crud->copyVarsFromRequest( MenuUtils::ACTIVE_MENU );

        $html = $crud->index();

        return $html;
    }

    public function recentAlarms() {
        $sql = <<<SQL
            SELECT *
            FROM tblModJackAlert
            WHERE fldStatus='new'
            ORDER BY fldTimeStamp
            LIMIT 50
SQL;
        $html = '<h2>Recent Alerts</h2>';

        $tab = new DBTable( DB::DEF, $sql, null, DB::FETCH_ASSOC );
        if ( $tab->isEmpty() ) {
            $html .= "There are no alerts at the moment";
        }
        else {
            $html.= Tag::table() .
                      Tag::tr () .
                        Tag::th() . 'Type' . Tag::_th() .
                        Tag::th() . 'Description' . Tag::_th() .
                        Tag::th() . 'Time' . Tag::_th() .
                        Tag::th() . 'Actions' . Tag::_th() .
                      Tag::_tr();
            $resp = MenuUtils::responseObject()->action( __CLASS__ . '->recentAlarmsAck()' );
            foreach ( $tab as $row ) {
                $style = [ 'class' => $row['fldType'] . '_row' ];
                $html .=
                      Tag::tr( $style ) .
                        Tag::td( $style ) . Tag::e( $row['fldType'] ) . Tag::_td() .
                        Tag::td( $style ) . Tag::e( $row['fldDescription'] ) . Tag::_td() .
                        Tag::td( $style ) . Tag::e( $row['fldTimeStamp'] ) . Tag::_td() .
                        Tag::td( $style ) .
                            Tag::linkButton( '?' . $resp->set( 'fldModJackAlertID', $row['fldModJackAlertID'] )->toUrl(), 'Ack' ) .
                        Tag::_td() .
                      Tag::_tr();
            }
            $html .= Tag::_table();
        }
        return $html;
    }
    public function recentAlarmsAck () {
        if ( ( $id = Request::get( 'fldModJackAlertID' ) ) == '' ||
             ( $alert = Alerts::get( $id ) ) === false ) {
            return $this->index() .
                   Widget::popupWrapper( 'Invalid Alert ID' );
        }

        $alert->status = Alerts::STATUS_SEEN;
        $alert->save();

        return $this->index() .
               Widget::popupWrapper( "Acknowledged Alert {$alert->id} - {$alert->desc}" );
    }

    public function todo () {
        $html = <<<HTML
  <table>
    <tr>
        <td>
            <h1>TODO</h1>
            <ol>
                <li>Item 1</li>
                <li>Item 2
                    <ul>
                        <li>Item 2.1</li>
                        <li>Item 2.2</li>
                    </ul>
                </li>
            </ol>
        </td>
    </tr>
</table>
HTML;
        return $html;
    }

    public function browse ( ) {
        $classes = array ();
        foreach ( ClassLocator::getDefaultClassLocator()->getLocatorArray() as $className => $fileName ) {
            if ( preg_match ( AutoLoader::THIRD_PARTY_REGEX, $fileName ) ) continue;
            $classes[$fileName] = $className;
        }
        asort ( $classes );

        $html = '<H4>Below are a list of classes, Click on class to view source</h4>' .
                Tag::form ( array ( 'method' => 'get' ) ) .
                  MenuUtils::responseObject()->action ( __CLASS__ . '->' . __FUNCTION__ . '()' )->toHidden ( false ) .
                  Lists::select ( 'fldFileName',
                                  $classes,
                                  array ( 'size' => '7','onClick' => 'submit();' ) ) .
                Tag::_form ();


        return $html .
               $this->sourceCode();
    }
    public function sourceCode (  ) {
        $fileName = Request::get( 'fldFileName', __FILE__ );
        $code = strtr( file_get_contents( $fileName ), array( '&' => '&amp;','<' => '&lt;' ) );
        // http://sunlightjs.com/
        $html = <<<HTML
            <link rel="stylesheet" type="text/css" href="http://www.brettdutton.com/prism/themes/sunlight.default.css" />
            <script type="text/javascript" src="http://www.brettdutton.com/prism/sunlight-min.js"></script>
            <script type="text/javascript" src="http://www.brettdutton.com/prism/lang/sunlight.php-min.js"></script>
            <pre class="sunlight-highlight-php">$code</pre>
            <script type="text/javascript">Sunlight.highlightAll( );</script>
HTML;
        return $html;
    }
}
