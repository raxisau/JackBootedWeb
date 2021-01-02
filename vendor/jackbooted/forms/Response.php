<?php

namespace Jackbooted\Forms;

use \Jackbooted\Html\Tag;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Security\Cryptography;
use \Jackbooted\Security\CSRFGuard;
use \Jackbooted\Security\TamperGuard;

/**
 * @copyright Confidential and copyright (c) 2021 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */

/**
 * Response - Created Hidden variables and URLs for the Response Vars
 */
class Response extends PipeLine {

    const UNIQUE_CSRF = 'unique guard';

    private static $crossSiteGuard = null;
    private static $ignoreCrossSiteGuard = false;
    private $intermediateUrlArray;
    private $intermediateHiddenArray;
    private $exemptKeys = [];

    /**
     * @param  $initPattern
     * @return Response
     */
    public static function factory( $initPattern = null ) {
        return new Response( $initPattern );
    }

    /**
     * @param  $initPattern
     * @return void
     */
    public function __construct( $initPattern = null ) {
        parent::__construct();
        // If there is a pattern passed thru then copy from request
        if ( $initPattern != null )
            $this->copyVarsFromRequest( $initPattern );

        $this->copyVarsFromRequest( WebPage::SAVE_URL );

        // Ensure that the known fields
        foreach ( TamperGuard::$knownFields as $key ) {
            $this->copyVarsFromRequest( $key );
            $this->addExempt( $key );
        }
    }

    /**
     * @param  $key
     * @return void
     */
    public function addExempt( $key ) {
        $this->exemptKeys[$key] = true;
        return $this;
    }

    /**
     * @param  $key
     * @param  $val
     * @param bool $encrypt
     * @return Response
     */
    public function set( $key, $val ) {
        $this->formVars[$key] = $val;
        return $this;
    }

    /**
     * @param  $val
     * @return Response
     */
    public function action( $val ) {
        $this->formVars[WebPage::ACTION] = $val;
        return $this;
    }

    /**
     * @param  $key
     * @return Response
     */
    public function del( $key ) {
        unset( $this->formVars[$key] );
        return $this;
    }

    /**
     * @param string $matches
     * @return Response
     */
    public function copyVarsFromRequest( $matches = '/.*/' ) {
        if ( !preg_match( '/^\\/.*\\/$/', $matches ) )
            $matches = '/^' . $matches . '$/';

        foreach ( Request::get() as $key => $val ) {
            if ( preg_match( $matches, $key ) )
                $this->set( $key, $val );
        }
        return $this;
    }

    private function addCSRFGuard() {
        if ( self::$ignoreCrossSiteGuard )
            return;

        if ( self::$crossSiteGuard == null ) {
            self::$crossSiteGuard = CSRFGuard::key();
        }
        $this->set( CSRFGuard::KEY, self::$crossSiteGuard );
    }

    private function delCSRFGuard() {
        $this->del( CSRFGuard::KEY );
    }

    /**
     * @return string
     */
    public function toHidden( $guard = true ) {
        if ( $guard )
            $this->addCSRFGuard();
        TamperGuard::add( $this );

        $html = '';
        $this->convertFormVarsToAssocArray();
        foreach ( $this->intermediateHiddenArray as $key => $val ) {
            $cypherText = $this->encryptValue( $key, $val );
            $html .= Tag::hidden( $key, $cypherText );
        }

        TamperGuard::del( $this );
        if ( $guard )
            $this->delCSRFGuard();
        return $html;
    }

    private function convertFormVarsToAssocArray() {
        $this->intermediateHiddenArray = [];
        foreach ( $this->formVars as $key => $val ) {
            $this->arrayWalkerToConvertToAssoc( $key, $val );
        }
    }

    private function arrayWalkerToConvertToAssoc( $key, &$value, $prefix = '' ) {
        $subKey = ( $prefix == '' ) ? $key : '[' . $key . ']';
        $compoundKey = $prefix . $subKey;

        if ( is_array( $value ) ) {
            foreach ( $value as $key => $val ) {
                $this->arrayWalkerToConvertToAssoc( $key, $val, $compoundKey );
            }
        }
        else {
            $this->intermediateHiddenArray[$compoundKey] = $value;
        }
    }

    /**
     * @return string
     */
    public function toUrl( $guard = false ) {
        if ( $guard === true )
            $this->addCSRFGuard();
        else if ( $guard == self::UNIQUE_CSRF ) {
            $tempGuard = self::$crossSiteGuard;
            self::$crossSiteGuard = null;
            $this->addCSRFGuard();
            self::$crossSiteGuard = $tempGuard;
        }

        TamperGuard::add( $this );
        $this->convertFormVarsToFlatArray();
        TamperGuard::del( $this );
        if ( $guard )
            $this->delCSRFGuard();
        return join( '&', $this->intermediateUrlArray );
    }

    public function __toString() {
        return $this->toUrl();
    }

    private function convertFormVarsToFlatArray() {
        $this->intermediateUrlArray = [];
        foreach ( $this->formVars as $key => $val ) {
            $this->arrayWalkerToConvertToFlat( $key, $val );
        }
    }

    private function arrayWalkerToConvertToFlat( $key, &$value, $prefix = '' ) {
        $subKey = ( $prefix == '' ) ? $key : '[' . $key . ']';
        $compoundKey = $prefix . $subKey;

        if ( is_array( $value ) ) {
            foreach ( $value as $key => $val ) {
                $this->arrayWalkerToConvertToFlat( $key, $val, $compoundKey );
            }
        }
        else {
            $cypherText = $this->encryptValue( $compoundKey, $value );
            $this->intermediateUrlArray[] = $compoundKey . '=' . urlencode( $cypherText );
        }
    }

    private function encryptValue( $key, $value ) {
        if ( isset( $this->exemptKeys[$key] ) ) {
            return $value;
        }
        else {
            return Cryptography::en( $value );
        }
    }
}
