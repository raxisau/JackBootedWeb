<?php
require_once dirname ( __FILE__ ) . '/config.php';

error_reporting(-1);
ini_set("display_errors", 1);

echo ":e:y8oAx8qgFHG1iDJ0FIMAehD5KibycJH0j6UJuoLizXU= => ***" . \Jackbooted\Security\Cryptography::de( ":e:y8oAx8qgFHG1iDJ0FIMAehD5KibycJH0j6UJuoLizXU=" ) . "***\n";
echo "This is a test => ***" . $ct = \Jackbooted\Security\Cryptography::en( "This is a test" ) . "***\n";
echo "$ct => ***" . \Jackbooted\Security\Cryptography::de( $ct ) . "***\n";
