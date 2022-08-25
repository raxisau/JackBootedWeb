<?php

require_once ( __DIR__ . '/config.php' );
Jackbooted\Util\MenuUtils::slugRedirect( filter_input( INPUT_GET, 'S' ) );
