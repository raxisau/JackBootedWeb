<?php
function getFactors( $val ) {
    $ret = [];

    for ( $i=2; $i<$val; $i++ ) {
        if ( (int)( (double)$val / (double)$i ) == ( (double)$val / (double)$i ) ) {
            $ret[] = $i;
        }
    }
    return $ret;
}
//print_r ( getFactors ( 28 ) );

$divA = [];
for ( $i=1; $i<=30; $i++ ) {
    foreach ( getFactors( $i ) as $fac ) {
        $divA[] = $i . ' divided by ' . $fac . ' = ';
    }
}
shuffle( $divA );
//print_r( $divA );

for ( $i=0; $i<count($divA); $i++ ) {
    echo $divA[$i] . "\n";
}


