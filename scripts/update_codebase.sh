#!/bin/bash
source ./scripts/env.sh
LOCKDIR=/tmp/update_codebase.sh.lock

if ! mkdir $LOCKDIR; then
    echo "Lock failed"
    exit 1
fi

OLDVERSION=`$PHP ./jack.php ODCA:version`
UPDATES=`git pull | grep "Already up-to-date." | wc -l`
if [  $UPDATES -eq 0 ]; then
    $PHP ./jack.php DB:migrate
    /usr/sbin/chown -R _www:_www *
    chmod -R a+w _private
    chmod -R g+w _private

    # Update crontab if necessary
    CWD=`pwd`
    cat ./scripts/crontab.txt | sed 's|INSTALLDIR|'$CWD'|g' > /tmp/tcrontab_new
    crontab -l > /tmp/tcrontab_old
    DIFF=`diff /tmp/tcrontab_old /tmp/tcrontab_new`
    if [ "$DIFF" != "" ]; then
        echo "$(date) $0 new crontab installed"
        crontab /tmp/tcrontab_new
    fi;
    rm /tmp/tcrontab_new /tmp/tcrontab_old

    NEWVERSION=`$PHP ./jack.php ODCA:version`
    echo "$(date) $0 Updated code base and migrated database"
    echo "$(date) $0 Old Version: $OLDVERSION"
    echo "$(date) $0 New Version: $NEWVERSION"
else
    echo "$(date) $0 No Software Updates"
fi

rmdir $LOCKDIR

