<?php
$config = [];
require_once( dirname( __FILE__ ) . '/config.env.php' );

if ( ! isset( $_SERVER['HTTP_X_HUB_SIGNATURE'] ) ) exit;

list( $algo, $hash ) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');
$rawPost = file_get_contents( 'php://input' );
if ( $hash !== hash_hmac( $algo, file_get_contents('php://input'), $config['githubsecret'] ) ) exit;
    
$Name      = "Push Callback"; //senders name 
$email     = "github@brettdutton.com"; //senders e-mail adress 
$recipient = "brett@brettdutton.com"; //recipient 
$mail_body = $_SERVER['HTTP_X_HUB_SIGNATURE'] . "\n" . 
             system( 'cd ' . dirname( __FILE__ ) . '; /usr/bin/git pull' ); //mail body 
$subject   = "Jack Commit " . date( "Y-m-d H:i:s" ); //subject 
$header    = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields 

mail($recipient, $subject, $mail_body, $header);