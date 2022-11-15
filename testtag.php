<?php

require_once __DIR__ . '/config.php';

error_reporting( -1 );
ini_set( "display_errors", 1 );

use \Jackbooted\Html\Tag;

echo 'Tag::_form() => ' . Tag::_form() . "\n";
echo 'Tag::_hTag( iframe )  => ' . Tag::_hTag( 'iframe' )  . "\n";
echo 'Tag::_iframe()  => ' . Tag::_iframe()  . "\n";
echo 'Tag::_table()  => ' . Tag::_table()  . "\n";
echo 'Tag::_tr()  => ' . Tag::_tr()  . "\n";
echo "Tag::_td()  => " . Tag::_td()  . "\n";
echo "Tag::_span()  => " . Tag::_span()  . "\n";
echo "Tag::hTag( 'iframe', [ 'src' => http://x.com/img.html, 'width' => '100%' ] )  => " . Tag::hTag( 'iframe', [ 'src' => "http://x.com/img.html", 'width' => '100%' ] )  . "\n";
echo "Tag::iframe( [ 'src' => http://x.com/img.html, 'width' => '100%' ] )  => " . Tag::iframe( [ 'src' => "http://x.com/img.html", 'width' => '100%' ] )  . "\n";
echo "Tag::img( http://x.com/img.jpg )  => " . Tag::img( "http://x.com/img.jpg" )  . "\n";
echo "Tag::submit( 'New Registration' )  => " . Tag::submit( 'New Registration' )  . "\n";
echo "Tag::table( [ 'align' => 'center', 'border' => 0, 'cellspacing' => 0, 'cellpadding' => 2 ] )  => " . Tag::table( [ 'align' => 'center', 'border' => 0, 'cellspacing' => 0, 'cellpadding' => 2 ] )  . "\n";
echo "Tag::td( [ 'colspan' => 2 ] )  => " . Tag::td( [ 'colspan' => 2 ] )  . "\n";
echo "Tag::td( [ 'colspan' => 2, 'align' => 'center' ] )  => " . Tag::td( [ 'colspan' => 2, 'align' => 'center' ] )  . "\n";
echo "Tag::td( [ 'colspan' => 2, 'align' => 'center' ] )  => " . Tag::td( [ 'colspan' => 2, 'align' => 'center' ] )  . "\n";
echo "Tag::td( [ 'colspan' => 2, 'nowrap' => 'nowrap', 'valign' => 'top' ] )  => " . Tag::td( [ 'colspan' => 2, 'nowrap' => 'nowrap', 'valign' => 'top' ] )  . "\n";
echo "Tag::td()  => " . Tag::td()  . "\n";
echo "Tag::text( 'fldCaptcha' )  => " . Tag::text( 'fldCaptcha' )  . "\n";
echo "Tag::text( 'fldEmail', Request::get( 'fldEmail' ) )  => " . Tag::text( 'fldEmail', 'Email' )  . "\n";
echo "Tag::text( 'fldFirstName', Request::get( 'fldFirstName' ) )  => " . Tag::text( 'fldFirstName', 'FirstName' )  . "\n";
echo "Tag::text( 'fldLastName', Request::get( 'fldLastName' ) )  => " . Tag::text( 'fldLastName', 'LastName' )  . "\n";
echo "Tag::tr()  => " . Tag::tr()  . "\n";
echo "Tag::br()  => " . Tag::br()  . "\n";
echo "Tag::li([ 'colspan' => 2, 'nowrap' => 'nowrap', 'valign' => 'top' ])  => " . Tag::li([ 'colspan' => 2, 'nowrap' => 'nowrap', 'valign' => 'top' ])  . "\n";
echo "Tag::span([ 'colspan' => 2, 'nowrap' => 'nowrap', 'valign' => 'top' ])  => " . Tag::span([ 'colspan' => 2, 'nowrap' => 'nowrap', 'valign' => 'top' ])  . "\n";
echo "Tag::hRef( 'https://href.li', '<img src=/assets/img/asicfavicon.ico>', [ 'target' => '_blank', 'title' => 'Search ASIC Record' ]  => \n       " . Tag::hRef( 'https://href.li', '<img src="/assets/img/asicfavicon.ico">', [ 'target' => '_blank', 'title' => 'Search ASIC Record' ] )  . "\n";
