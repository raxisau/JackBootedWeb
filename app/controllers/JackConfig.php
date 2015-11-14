<?php
namespace App\Controllers;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Util\MenuUtils;
use \Jackbooted\Forms\Request;
use \Jackbooted\Config\Config;
use \Jackbooted\Html\Widget;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\JS;
use \Jackbooted\Html\Lists;
use \Jackbooted\DB\DB;

class JackConfig extends WebPage {
    const DEF = '\App\Controllers\JackConfig->index()';

    public static function menu ( $resp=null ) {
        if ( $resp == null ) $resp = MenuUtils::responseObject ();

        return [ [ 'name'    => 'Edit Configuration',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->index()' ),
                   'attribs' =>  [ 'title' => 'Edits configuration that controls this Appliance.' ] ],
                 [ 'name'    => 'Reset',
                   'url'     => '?' . $resp->action ( __CLASS__ . '->reset()' ),
                   'attribs' =>  [ 'title'   => 'Reset all the configuration.',
                                   'onClick' => "return confirm('***WARNING*** This will erase all configuration')" ] ],
               ];
    }
    public function reset ( ) {
        DB::exec( DB::DEF, 'DELETE FROM tblConfig' );
        return Widget::popupWrapper( 'All configuration data has been erased' ) .
               $this->index();
    }

    public function index ( ) {
        $html = '<h2 title="You are able to edit all your defaults WARNING please know what you are doing">JSON Configuration Editor</h2>';

        if ( ( $currentConfigKey = Request::get ( 'fldCfgKey' ) ) == '' ) {
            $currentConfigKey = DB::oneValue(DB::DEF, 'SELECT fldKey FROM tblConfig ORDER BY 1 LIMIT 1' );
        }
        if ( $currentConfigKey === false || $currentConfigKey == '' ) {
            return $html .
                   'No Configuration available yet';
        }

        $html .= Tag::table (  [ 'border' => '0', 'height' => '100%', 'width' => '100%']) .
                   Tag::tr( ) .
                     Tag::td( [ 'nowrap' => 'nowrap', 'valign' => 'top' ] ) .
                       $this->editConfigForm ( $currentConfigKey ) .
                     Tag::_td () .
                     Tag::td(  [ 'width' => '100%', 'valign' => 'top' ] ) .
                       $this->editJSONEditForm ( $currentConfigKey ) .
                     Tag::_td () .
                   Tag::_tr () .
                 Tag::_table ();

        return $html;
    }

    public function editConfigForm ( $currentConfigKey ) {
        return  JS::library( JS::JQUERY ) .
                JS::javaScript( "$().ready( function(){ $('#fldCfgKey').focus (); });" ) .
                Tag::hTag ( 'b' ) . 'Config Keys' . Tag::_hTag ( 'b' ) .
                Tag::form (  [ 'method' => 'get' ] ) .
                  MenuUtils::responseObject ()
                           ->action ( __CLASS__ . '->edit()' )
                           ->toHidden ( false ) .
                  Lists::select ( 'fldCfgKey',
                                  'SELECT fldKey FROM tblConfig ORDER BY 1',
                                   [ 'style'    => 'height: 100%',
                                          'default'  => $currentConfigKey,
                                          'size'     => 26,
                                          'id'       => 'fldCfgKey',
                                          'onChange' => 'submit();' ] ) .
                Tag::_form () .
                '<br/>' .
                Tag::hRef( '?' . MenuUtils::responseObject ()
                                         ->action ( __CLASS__ . '->reload()' )
                                         ->toUrl(),
                           'Reload Config',
                            [ 'title'   => 'reloads the configuration',
                                   'onClick' => 'return confirm("Are You Sure you want to reload all configuration?")' ] );
    }

    public function editJSONEditForm ( $currentConfigKey ) {
        $json = json_encode( Config::get( $currentConfigKey ) );
        $js = <<< JS
            var json = $json;
            main.load(json);
            main.resize();
JS;
        return JS::library( 'jsoneditor.css' ) .
               JS::library( 'interface.css' ) .
               JS::library( 'jsoneditor.js' ) .
               JS::library( 'interface.js' ) .
               Tag::div(  [ 'id' => 'auto' ] ) .
                 Tag::div(  [ 'id' => 'contents', 'height' => '100%' ] ) .
                   Tag::table ( [ 'border' => '0', 'height' => '100%', 'width' => '100%']) .
                     Tag::tr ( ) .
                       Tag::td (  [ 'valign' => 'top', 'width' => '45%', 'height' => '100%' ] ) .
                         Tag::div(  [ 'id' => 'jsonformatter' ] ) . Tag::_div () .
                       Tag::_td () .
                       Tag::td (  [ 'valign' => 'top', 'width' => '10%', 'align' => 'center' ] ) .
                         Tag::div(  [ 'id' => 'splitter' ] ) . Tag::_div () .
                       Tag::_td () .
                       Tag::td (  [ 'valign' => 'top', 'width' => '45%', 'height' => '100%' ] ) .
                         Tag::div(  [ 'id' => 'jsoneditor' ] ) . Tag::_div () .
                       Tag::_td () .
                     Tag::_tr() .
                   Tag::_table ().
                 Tag::_div() .
               Tag::_div() .
               Tag::form() .
                 MenuUtils::responseObject ()
                          ->set( 'fldCfgKey', $currentConfigKey )
                          ->action ( __CLASS__ . '->saveConfig()' )
                          ->toHidden () .
                 Tag::textArea( 'fldCfgValue', '',  [ 'id' => 'fldCfgValue', 'style' => 'display: none;' ] ) .
                 '<b>Currently editing : <i>' . $currentConfigKey . '</i></b> ' .
                 Tag::submit( 'Save',  [ 'onClick' => "$('#fldCfgValue').val(JSON.stringify(JSON.parse($('textarea.jsonformatter-textarea').val()))); return 1;" ] ) .
               Tag::_form() .
               // textarea class="jsonformatter-textarea
               JS::javaScript( $js );
    }
    public function saveConfig ( ) {
        Config::put( Request::get ( 'fldCfgKey' ), json_decode( Request::get ( 'fldCfgValue' ), true ) );

        return Widget::popupWrapper ( 'Saved Config Item: ' . Request::get ( 'fldCfgKey' ), 1000, 'Save Config Message' ) .
               $this->edit ();
    }
}


