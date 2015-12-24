<?php
namespace Jackbooted\Mail;

class SMTPSendMail extends \PHPMailer {
    public function __construct ( $host='localhost', $exceptions = false ) {
        parent::__construct ( $exceptions );

        $this->isSMTP();
        $this->SMTPDebug = 0;

        //Ask for HTML-friendly debug output
        $this->Debugoutput = 'html';

        //Set the hostname of the mail server
        $this->Host = $host;

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->Port = 25;

        //Whether to use SMTP authentication
        $this->SMTPAuth = false;
    }
}