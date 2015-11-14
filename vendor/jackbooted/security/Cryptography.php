<?php
namespace Jackbooted\Security;

use \Jackbooted\Config\Cfg;
use \Jackbooted\G;
use \Jackbooted\Util\Log4PHP;
/*
 * @copyright Confidential and copyright (c) 2015 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */

/**
 * Description of Encryption
 *
 * @author bdutton
 */
class Cryptography extends \Jackbooted\Util\JB {

    //echo str_shuffle ( 'PredefinedEncryptionKey12345678901234567' );
    const RAND_KEY = '4n5d73rde315E486pe9t7oy2nirefcPKnie0y126';

    const META = ':e:';
    const META_LEN = 3;
    const PADDING = '                                                  ';

    private static $randKey;
    private static $instance = null;
    private static $log;
    private static $encryptionOff = false;
    private static $algortithm;

    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
        self::$encryptionOff = Cfg::get ( 'encrypt_override' );
        if ( ! function_exists ( 'mcrypt_get_key_size' ) ) {
            self::$encryptionOff = true;
        }

        // The IV is session specific. See if the key has been set in the session
        if ( isset ( $_SESSION[G::SESS][G::CRYPTO] ) ) {
            self::$randKey = md5 ( $_SESSION[G::SESS][G::CRYPTO] );
        }
        else {
            self::$randKey = md5 ( self::RAND_KEY );
            self::$log->warn ( 'Using the default key for crypto' );
        }

        if ( ! self::$encryptionOff ) {
            self::$algortithm = ( Cfg::get ( 'quercus', false ) ) ? MCRYPT_TRIPLEDES : MCRYPT_RIJNDAEL_256;
        }

        self::$instance = new Cryptography ( self::$randKey );
    }

    /**
     * General encryption method
     * @param string $plainText
     * @return string
     */
    public static function en ( $plainText ) {
        return self::$instance->encrypt ( $plainText );
    }

    /**
     * generalized decryption methd.
     * @param string $cypherText
     * @return string
     */
    public static function de ( $cypherText ) {
        return self::$instance->decrypt ( $cypherText );
    }

    public static function factory ( $l_key=null ) {
        return new Cryptography( $l_key );
    }

    private $td;
    private $blockLength;

    /**
     *
     * @param string $l_key Key to use for encryption
     */
    public function __construct ( $l_key=null ) {
        parent::__construct();

        if ( self::$encryptionOff ) {
            return;
        }

        $plainTextKey = ( $l_key == null ) ? self::$randKey : $l_key;

        $keySize = mcrypt_get_key_size ( self::$algortithm, MCRYPT_MODE_ECB );
        while ( strlen ( $plainTextKey ) < $keySize ) $plainTextKey .= $plainTextKey;
        $plainTextKey = substr ( $plainTextKey, 0, $keySize );

        $this->blockLength = mcrypt_get_block_size ( self::$algortithm, MCRYPT_MODE_ECB );
        $this->td = mcrypt_module_open ( self::$algortithm, '', MCRYPT_MODE_ECB, '' );

        $iv = mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $this->td ), MCRYPT_RAND );
        mcrypt_generic_init ( $this->td, $plainTextKey, $iv );
    }
    /**
     * encrypt the passed string
     * @param string $input
     * @return string
     */
    public function encrypt ( $plainText, $force=false ) {
        if ( self::$encryptionOff && ! $force ) return $plainText;
        if ( ! isset ( $plainText ) || strlen ( $plainText ) == 0 ) {
            return $plainText;
        }

        $len = strlen ( $plainText ) % $this->blockLength;
        if ( $len != 0 ) {
            $charactersNeeded = $this->blockLength - $len;
            $plainText .= substr ( self::PADDING, 0, $charactersNeeded );
        }

        $m = mcrypt_generic ( $this->td, $plainText );
        $cypherText = self::META . base64_encode ( $m );
        return $cypherText;
    }

    /**
     * decrypt passed string
     * @param string $input
     * @return string
     */
    public function decrypt ( $cypherText ) {
        if ( strpos ( $cypherText, self::META ) !==  0 ) return $cypherText;
        $plainText = trim ( mdecrypt_generic ( $this->td, base64_decode ( substr ( $cypherText, self::META_LEN ) ) ) );
        return $plainText;
    }

    /**
     * Clean up the crypto resources when they go out of scope
     */
    public function __destruct() {
        if ( function_exists ( 'mcrypt_generic_deinit' ) && $this->td != null ) mcrypt_generic_deinit ( $this->td );
        if ( function_exists ( 'mcrypt_module_close' ) && $this->td != null ) mcrypt_module_close ( $this->td );
    }
}