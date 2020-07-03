<?php

namespace Jackbooted\Mail;

/**
 * @copyright Confidential and copyright (c) 2020 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */
class NMailer extends \Jackbooted\Util\JB {

    function send( $mailTo, $fromMail, $fromName, $subject, $message, $replyTo=null, $filePath=null ) {

        $LE = "\r\n";
        $uid = md5( uniqid( time() ) );
        $withAttachment = ($filePath !== NULL && file_exists( $filePath ));

        if ( $withAttachment ) {
            $fileName = basename( $filePath );
            $fileSize = filesize( $filePath );
            $handle = fopen( $filePath, "r" );
            $content = chunk_split( base64_encode( fread( $handle, $fileSize ) ) );
            fclose( $handle );
        }

        $header = "From: " . $fromName . " <" . $fromMail . ">$LE";
        if ( $replyTo != null ) $header .= "Reply-To: " . $replyTo . "$LE";
        $header .= "MIME-Version: 1.0$LE";
        $header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"$LE$LE";
        $header .= "This is a multi-part message in MIME format.$LE";
        $header .= "--" . $uid . "$LE";
        $header .= "Content-type:text/html; charset=UTF-8$LE";
        $header .= "Content-Transfer-Encoding: 7bit$LE$LE";
        $header .= $message . "$LE$LE";

        if ( $withAttachment ) {
            $header .= "--" . $uid . "$LE";
            $header .= "Content-Type: application/octet-stream; name=\"" . $fileName . "\"$LE";
            $header .= "Content-Transfer-Encoding: base64$LE";
            $header .= "Content-Disposition: attachment; filename=\"" . $fileName . "\"$LE$LE";
            $header .= $content . "$LE$LE";
            $header .= "--" . $uid . "--";
        }
        return mail( $mailTo, $subject, strip_tags( $message ), $header );
    }
}
