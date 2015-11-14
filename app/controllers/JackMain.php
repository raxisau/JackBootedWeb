<?php
namespace App\Controllers;

use \Jackbooted\DB\DB;
use \Jackbooted\DB\DBTable;
use \Jackbooted\Forms\Request;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Html\Widget;
use \Jackbooted\Util\MenuUtils;

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
                 [ 'name'    => 'TODO List',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->todo()' ),
                   'attribs' =>  [ 'title' => 'List of outstanding items' ] ],
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
}
