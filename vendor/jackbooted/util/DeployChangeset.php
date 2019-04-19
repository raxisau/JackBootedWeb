<?php

namespace Jackbooted\Util;

/**
 * @copyright Confidential and copyright (c) 2019 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */
class DeployChangeset {

    public static function init() {

    }

    private $targetDirectory;
    private $conn_id;
    public $csDetails;
    private $testMode;
    private $messages;

    public function __construct( $targetDirectory, $testMode = true ) {
        parent::__construct();
        $this->targetDirectory = $targetDirectory;
        $this->testMode = $testMode;
    }

    public function findChangeset( $changeset ) {
        $cmdResult = shell_exec( sprintf( 'svn diff -c %d --summarize', $changeset ) );

        $this->csDetails = [ 'file' => [], 'dir' => [] ];
        foreach ( explode( "\n", $cmdResult ) as $line ) {
            if ( ( $file = trim( substr( $line, 2 ) ) ) == '' )
                continue;
            if ( ( $action = trim( substr( $line, 0, 2 ) ) ) == '' )
                continue;

            $file = str_replace( "\\", '/', $file );
            $type = ( is_dir( $file ) ) ? 'dir' : 'file';
            $this->csDetails[$type][$file] = $action;
        }

        return $this->csDetails;
    }

    private function addDir( $dir, $display ) {
        $chDir = $this->targetDirectory . dirname( $dir );
        $mkDir = basename( $dir );

        if ( $this->testMode ) {
            $this->messages[] = "Test mode, Add directory, $mkDir to $chDir";
            if ( $display )
                echo end( $this->messages ), "\n";
        }
        else {
            if ( @ftp_chdir( $this->conn_id, $chDir ) === false ) {
                $this->messages[] = "Could not change directory to $chDir";
                if ( $display )
                    echo end( $this->messages ), "\n";
            }
            else if ( ( $newDir = @ftp_mkdir( $this->conn_id, $mkDir ) ) === false ) {
                $this->messages[] = "Could not Add directory, $mkDir to $chDir";
                if ( $display )
                    echo end( $this->messages ), "\n";
            }
            else {
                $this->messages[] = "Add directory, $mkDir to $chDir";
                if ( $display )
                    echo end( $this->messages ), "\n";
            }
        }
    }

    private function uploadFile( $file, $display ) {
        $remoteFile = $this->targetDirectory . $file;
        if ( $this->testMode ) {
            $this->messages[] = "Test mode, Uploaded $file to $remoteFile";
            if ( $display )
                echo end( $this->messages ), "\n";
        }
        else {
            if ( @ftp_put( $this->conn_id, $remoteFile, $file, FTP_BINARY ) ) {
                $this->messages[] = "Uploaded $file to $remoteFile";
                if ( $display )
                    echo end( $this->messages ), "\n";
            }
            else {
                $this->messages[] = "Could not Uploaded $file to $remoteFile";
                if ( $display )
                    echo end( $this->messages ), "\n";
            }
        }
    }

    private function deleteFile( $file, $display ) {
        $remoteFile = $this->targetDirectory . $file;
        if ( $this->testMode ) {
            $this->messages[] = "Test mode, Deleting $remoteFile sucessful";
            if ( $display )
                echo end( $this->messages ), "\n";
        }
        else {
            if ( @ftp_delete( $this->conn_id, $remoteFile ) ) {
                $this->messages[] = "Deleting $remoteFile sucessful";
                if ( $display )
                    echo end( $this->messages ), "\n";
            }
            else {
                $this->messages[] = "Could not Deleting $remoteFile";
                if ( $display )
                    echo end( $this->messages ), "\n";
            }
        }
    }

    public function action( $display = false ) {
        $this->messages = [];

        // Deal with Directories first
        ksort( $this->csDetails['dir'] );
        foreach ( $this->csDetails['dir'] as $dir => $action ) {
            if ( $action == 'A' )
                $this->addDir( $dir, $display );
        }

        // Sort out files
        foreach ( $this->csDetails['file'] as $file => $action ) {
            switch ( $action ) {
                case 'A':
                case 'M': $this->uploadFile( $file, $display );
                    break;
                case 'D': $this->deleteFile( $file, $display );
                    break;
            }
        }
        return $this->messages;
    }

    public function connect( $hostname, $username, $password ) {
        if ( ( $this->conn_id = ftp_connect( $hostname ) ) === false ) {
            return 'Failed to find server';
        }
        else if ( @ftp_login( $this->conn_id, $username, $password ) === false ) {
            return 'Could not connect with username and password';
        }
        @ftp_pasv( $this->conn_id, true );
        return true;
    }

    public function disconnect() {
        @ftp_close( $this->conn_id );
    }

}
