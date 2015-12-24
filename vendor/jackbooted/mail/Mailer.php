<?php
namespace Jackbooted\Mail;

use \Jackbooted\Util\Log4PHP;
/**
 * @copyright Confidential and copyright (c) 2016 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */

class Mailer extends \Jackbooted\Util\JB {
    const PLAIN_TEXT = 't';
    const HTML_TEXT  = 'h';

    private static $LF = "\r\n";
    private static $log;

    public static function init () {
        self::$log = Log4PHP::logFactory ( __CLASS__ );
    }

    public static function envelope () {
        return new Mailer ();
    }

    private $toVar;
    private $fromVar;
    private $subjectVar;
    private $bodyVar;
    private $msgFormat = self::PLAIN_TEXT;

    public function __construct () {
        parent::__construct();
    }

    public function from ( $f ) {
        $this->fromVar = $f;
        return $this;
    }
    public function to ( $t ) {
        $this->toVar = $t;
        return $this;
    }
    public function subject ( $s ) {
        $this->subjectVar = $s;
        return $this;
    }
    public function body ( $b ) {
        $this->bodyVar = $b;
        return $this;
    }
    public function format ( $f ) {
        $this->msgFormat = $f;
        return $this;
    }
    public function send () {
        self::$log->debug ( 'To: '      . $this->toVar );
        self::$log->debug ( 'From: '    . $this->fromVar );
        self::$log->debug ( 'Subject: ' . $this->subjectVar );
        self::$log->debug ( 'Message: ' . $this->bodyVar );

        $h = '';
        if ( $this->msgFormat == self::HTML_TEXT ) {
            $h .= 'MIME-Version: 1.0' . self::$LF;
            $h .= 'Content-type: text/html; charset=iso-8859-1' . self::$LF;
        }
        $h .= 'From: ' . $this->fromVar . self::$LF;

        if ( ! mail ( $this->toVar,  $this->subjectVar,  $this->bodyVar, $h ) ) {
            self::$log->error ( "Error sending mail {$this->fromVar} {$this->toVar} {$this->subjectVar}" );
        }

        return $this;
    }
}
