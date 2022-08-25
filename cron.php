<?php

use \Jackbooted\Cron\Cron;
use \Jackbooted\Cron\CronDAO;
use \Jackbooted\Cron\Scheduler;

// /usr/bin/php /home/brettdut/public_html/voodoo/cron.php >> /home/brettdut/public_html/voodoo/_private/cron.log

require_once __DIR__ . "/config.php";
set_time_limit( 0 );
if ( isset( $_SERVER['HTTP_HOST'] ) )
    echo '<pre>';
_runner();
if ( isset( $_SERVER['HTTP_HOST'] ) )
    echo '</pre>';

function _runner() {
    _runnerLog( 'Running Cron' );
    $pageTimer = new \Jackbooted\Time\Stopwatch( 'Run time for ' . basename( __FILE__ ) );
    _runnerLog( 'Checking if scheduled jobs need to be added to CronQueue' );
    Scheduler::check();
    $numberOfItemsProcessed = 0;

    while ( $pageTimer->getTime() < 60 ) {
        $cronJobList = Cron::getList( 1 );
        if ( count( $cronJobList ) <= 0 )
            break;

        foreach ( $cronJobList as $cronJob ) {
            _runnerLog( 'Found Job: ' . $cronJob->id );
            flush();

            _runnerLog( 'Changing the status to RUNNING for JobID: ' . $cronJob->id );
            $cronJob->status = CronDAO::STATUS_RUNNING;
            $cronJob->runTime = time();
            $cronJob->save();
            flush();

            $cronJob->result = -1;
            $cronJob->message = '';

            _runnerLog( 'Running command: ' . $cronJob->command . ' ID:' . $cronJob->id );
            unset( $result );
            @eval( '$result = ' . $cronJob->command );

            if ( isset( $result ) && is_array( $result ) ) {
                $cronJob->result = $result[0];
                $cronJob->message = $result[1];
            }

            _runnerLog( 'Finished Job ID: ' . $cronJob->id .
                    ' Result: ' . $cronJob->result .
                    ' Message: ' . $cronJob->message );
            $cronJob->status = CronDAO::STATUS_COMPLETE;
            $cronJob->save();
            flush();

            $numberOfItemsProcessed++;
        }
    }
    _runnerLog( 'Processed ' . $numberOfItemsProcessed . ' items.' );
    _runnerLog( $pageTimer->logLoadTime() );
}

function _runnerLog( $msg ) {
    echo date( 'd-m-Y H:i:s' ), ' ', $msg, "\n";
}
