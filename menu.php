<?php

require_once ( dirname( __FILE__ ) . '/config.php' );
Jackbooted\Util\MenuUtils::slugRedirect( filter_input( INPUT_GET, 'S' ) );
