<?php
namespace App\Controllers;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Util\MenuUtils;
use \Jackbooted\Forms\Request;
use \Jackbooted\Config\Config;
use \Jackbooted\Config\Cfg;
use \Jackbooted\Html\Widget;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\JS;
use \Jackbooted\Html\Lists;
use \Jackbooted\DB\DB;

class JackConfig extends WebPage {
    const DEF = '\App\Controllers\JackConfig->index()';

    public static function menu ( $resp=null ) {
        if ( $resp == null ) $resp = MenuUtils::responseObject ();

        return [ [ 'name'    => 'Edit Config JSON',
                   'url'     => '?' . $resp->action ( '\Jackbooted\Config\ConfigManager->index()' ),
                   'attribs' =>  [ 'title' => 'Edits configuration that controls this Application' ] ],

                 [ 'name'    => 'Edit Config Raw',
                   'url'     => '?' . $resp->action ( '\Jackbooted\Config\AdminConfig->index()' ),
                   'attribs' =>  [ 'title' => 'Edits configuration that controls this Application' ] ],

                 [ 'name'    => 'Reset',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->reset()' ),
                   'attribs' =>  [ 'title' => 'Reset', 'onClick' => "return confirm('Are you sure you wish to reset?')" ] ],
               ];
    }
    public function reset ( ) {
        $resp = MenuUtils::responseObject ();
        $html = '';
        $html .= '<H4>***WARNING*** You are about to reset configuration</h4>' .
                 Tag::form ( ) .
                   $resp->action ( sprintf ( '%s->%sSave()', __CLASS__, __FUNCTION__ ) )->toHidden () .
                   Tag::table ( ) .
                     Tag::tr ( ) .
                       Tag::td () . 'Please type "RESET CONFIG" without the quotes for finalise reset.' . Tag::_td () .
                       Tag::td () . Tag::text ( 'fldConfirm' ) . Tag::_td () .
                     Tag::_tr () .
                     Tag::tr ( ) .
                       Tag::td ( ['colspan' => 2 ]) .
                         Tag::submit( 'Confirm Reset' ) .
                         Tag::linkButton('?' . $resp->action( __CLASS__ . '->resetCancelled()' ), 'Cancel Reset' ) .
                       Tag::_td () .
                     Tag::_tr () .
                   Tag::_table () .
                 Tag::_form ();

        return $html;
    }

    public function resetCancelled ( ) {
        return Widget::popupWrapper( 'Device Reset Cancelled', -1, 'Action Cancelled' ) .
               $this->index();
    }

    public function resetSave ( ) {
        if ( ( $confirm = Request::get ( 'fldConfirm' ) ) == '' ||
             $confirm != 'RESET CONFIG' ) {
            return Widget::popupWrapper( 'Invalid response, Reset cancelled', -1, 'Action Cancelled' ) .
                   $this->index();
        }
        else {
            DB::exec( DB::DEF, 'DELETE FROM tblConfig' );
            return Widget::popupWrapper( 'All configuration data has been erased', -1, 'Reset Complete' ) .
                   $this->index();
        }
    }
}
