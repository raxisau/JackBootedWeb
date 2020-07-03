<?php
require_once dirname( __FILE__ ) . '/config.php';

\Jackbooted\Mail\NMailer::send( 'brett@brettdutton.com',	'test@b2bconsultancy.com', 'Test User',	'This message is sent wth mail PHP',
        '<html><body><h1>This is a test Header</h1>Hi there earthlings</body></html>', dirname(__FILE__) . '/README.md' );
?>
Sent


