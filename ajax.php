<?php

/* This is designed for serving up ajax calls
 * It will return quickly if this is PHPLiceX call or calling
 * with action to return some data
 */
require_once __DIR__ . '/config.php';
// Check to see if this is a PHPLixeX call
PHPLiveX::create()->execute();

// If made it to here as PHPLiveX did not execute then
// check if we have any wep page actions
if ( ( $html = \Jackbooted\Html\WebPage::controller() ) !== false )
    echo $html;