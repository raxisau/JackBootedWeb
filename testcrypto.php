<?php

require_once dirname( __FILE__ ) . '/config.php';

error_reporting( -1 );
ini_set( "display_errors", 1 );

$oldVal = \Jackbooted\Config\Cfg::get( 'crypto_location' );
\Jackbooted\Config\Cfg::set( 'crypto_location', 'config' );
echo ":d:3vUCAIpsYzaecIhy+3dZSMgPCGWdGpTUD3FrZDHvm6keNNn2e8tCMiNkRnzI7XSWgdoOA9nc49yiXUj0AXmrYuDjbx1M8eX73i+GJ+uTzSL+jxHQ7tg6nvFXpMOe8/ZSPbE= => ***" . \Jackbooted\Security\Cryptography::de( ":d:3vUCAIpsYzaecIhy+3dZSMgPCGWdGpTUD3FrZDHvm6keNNn2e8tCMiNkRnzI7XSWgdoOA9nc49yiXUj0AXmrYuDjbx1M8eX73i+GJ+uTzSL+jxHQ7tg6nvFXpMOe8/ZSPbE=" ) . "***\n";
\Jackbooted\Config\Cfg::set( 'crypto_location', $oldVal );

echo "This is a test => ***" . $ct = \Jackbooted\Security\Cryptography::en( "This is a test" ) . "***\n";
echo "$ct => ***" . \Jackbooted\Security\Cryptography::de( $ct ) . "***\n";
