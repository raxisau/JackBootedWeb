<?php

$Name      = "Push Callback"; //senders name 
$email     = "github@brettdutton.com"; //senders e-mail adress 
$recipient = "brett@brettdutton.com"; //recipient 
$mail_body = system( 'cd ' . dirname( __FILE__ ) . '; /usr/bin/git pull' ); //mail body 
$subject   = "Jack Commit " . date( "Y-m-d H:i:s" ); //subject 
$header    = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields 

mail($recipient, $subject, $mail_body, $header); //mail command :) 