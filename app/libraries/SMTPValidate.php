<?php

namespace App\Libraries;


class SMTPValidate extends \Jackbooted\Util\JB {
    public static function isValid( $to, $from='support@b2bconsultancy.asia', $debugLevel=0 ) {
        $smtp = new \SMTP();

        $smtp->do_debug = $debugLevel;
        if ( $smtp->do_debug >= 3 ) {
            $smtp->edebug( "Debug Level: $debugLevel" );
        }

        $mxs = self::buildMxs( self::getDomain( $to ) );
        if ( $smtp->do_debug >= 3 ) {
            $smtp->edebug( print_r( $mxs, true ) );
        }
        foreach ( $mxs as $host => $weight ) {
            if ( $smtp->connect( $host ) ) {
                break;
            }
        }

        if ( ! $smtp->connected( ) ) return false;
        if ( $from == null || $from == '' ) $from = 'support@b2bconsultancy.asia';
        $host = self::getDomain( $from );
        if ( ! $smtp->hello( $host ) ) return false;
        if ( ! $smtp->mail( $from ) ) return false;
        if ( ! $smtp->recipient( $to ) ) return false;
        $smtp->reset();
        $smtp->close();

        return true;
    }

    private static function getDomain( $email ) {
        list( $user, $domain) = explode( '@', $email );
        return $domain;
    }

    private static function buildMxs( $domain ) {
        $mxs     = [];
        $hosts   = [];
        $weights = [];
        getmxrr( $domain, $hosts, $weights );

        // Sort out the MX priorities
        foreach ( $hosts as $k => $host ) {
            $mxs[$host] = $weights[$k];
        }
        asort( $mxs );

        // Add the hostname itself with 0 weight (RFC 2821)
        $mxs[$domain] = 0;
        print_r( $mxs );

        return $mxs;
    }

}

