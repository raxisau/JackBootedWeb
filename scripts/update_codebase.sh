#!/bin/bash
source ./scripts/env.sh
LOCKDIR=/tmp/update_codebase.sh.lock

if ! mkdir $LOCKDIR; then
    echo "Lock failed"
    exit 1
fi

OLDVERSION=`$PHP ./jack.php Jack:version`
UPDATES=`git pull | grep "Already up-to-date." | wc -l`
if [  $UPDATES -eq 0 ]; then
    $PHP ./jack.php DB::migrate
    chown -R _www:_www *
    chmod -R a+w _private
    chmod -R g+w _private
    NEWVERSION=`$PHP ./jack.php Jack:version`
    echo "$(date) $0 Updated code base and migrated database from $OLDVERSION to $NEWVERSION"
else
    echo "$(date) $0 No Updates"
fi

rmdir $LOCKDIR

