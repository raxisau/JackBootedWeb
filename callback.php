<?php

$Name      = "Commit Callback"; //senders name
$email     = "jack@brettdutton.com"; //senders e-mail adress
$recipient = "brett@brettdutton.com"; //recipient
$mail_body = system( 'cd ' . dirname( __FILE__ ) . '; /usr/bin/git pull' ); //mail body
$subject   = "Jackbooted Commit " . date( "Y-m-d H:i:s" ); //subject
$header    = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields

mail($recipient, $subject, $mail_body, $header); //mail command :) 