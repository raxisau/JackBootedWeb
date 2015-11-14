<?php
namespace App\Controllers;

use \Jackbooted\Forms\Request;
use \Jackbooted\Html\Lists;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Util\AutoLoader;
use \Jackbooted\Util\ClassLocator;
use \Jackbooted\Util\MenuUtils;

class JackBrowser extends WebPage {
    const DEF = '\App\Controllers\JackBrowser->index()';

    public static function menu ( $resp=null ) {
        if ( $resp == null ) $resp = MenuUtils::responseObject ();

        $menuItems = array ();

        $menuItems[] = array ( 'name'    => 'Browse Classes',
                               'url'     => '?' . $resp->action ( self::DEF ),
                               'slug'    => 'browse_classes',
                               'attribs' => array ( 'title' => 'Browse All Classes' ) );

        return $menuItems;
    }

    public function index ( ) {
        $classes = array ();
        foreach ( ClassLocator::getDefaultClassLocator()->getLocatorArray() as $className => $fileName ) {
            if ( preg_match ( AutoLoader::THIRD_PARTY_REGEX, $fileName ) ) continue;
            $classes[$fileName] = $className;
        }
        asort ( $classes );

        $html = '<H4>Below are a list of classes, Click on class to view source</h4>' .
                Tag::form ( array ( 'method' => 'get' ) ) .
                  MenuUtils::responseObject()->action ( __CLASS__ . '->index()' )->toHidden ( false ) .
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
