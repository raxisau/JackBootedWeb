#!/bin/bash
WEB_DIR=/Library/WebServer/Documents
INSTALL_DIR=$WEB_DIR/jack
LOG_DIR=/var/log
LOG_FILE=$LOG_DIR/jack.log
PHP=`which php`
ENV=$INSTALL_DIR/.env
TENV=/tmp/.env
ROTATE_CONF=/etc/newsyslog.d/jack.conf

#Check if we need to install mcrypt
MCRYPT=`$PHP -i | grep mcrypt`
PHPVER=`$PHP -version`
if [ "$MCRYPT" == "" ]; then
    echo "$(date) $0 ******************** Cannot Continue ***********************"
    echo "$(date) $0 Checked PHP and it looks like mcrypt has not been installed"
    echo "$(date) $0 This is really hard to do with scripts, so I need you to do it manually"
    echo "$(date) $0 Please check http://coolestguidesontheplanet.com they have step by step guides for installation of mcrypt"
    echo "$(date) $0 use the search string to find your version of OSX and PHP"
    echo "$(date) $0 Your PHP Version is: $PHPVER"
    echo "$(date) $0 Useful URLs:"
    echo "$(date) $0 http://coolestguidesontheplanet.com/install-mcrypt-php-mac-osx-10-10-yosemite-development-server/"
    echo "$(date) $0 http://sourceforge.net/projects/mcrypt/files/Libmcrypt/2.5.8/libmcrypt-2.5.8.tar.gz/download"
    echo "$(date) $0 http://php.net/releases/index.php"
    echo "$(date) $0 ************************************************************"
    exit 1
fi

echo "$(date) $0 getting your IP"
IP=`ifconfig -a | grep inet | grep -v inet6 | grep -v 127.0.0.1 | awk '/inet/{print $2}'`
echo "$(date) $0 Looks like your IP is $IP"

#**************************** Do you need beanstalkd? **************
# http://kr.github.io/beanstalkd/download.html
# If you need to check the command line for beanstalk then you can install this
# https://github.com/schickling/beanstalkd-cli/releases/download/0.3.0/beanstalkd-cli-osx.tar.gz
#**************************** Uncomment below if you do? **************
#echo "$(date) $0 Checking if we need to install beanstalk for queueing"
#if [ -e /usr/local/bin/beanstalkd ]; then
#    echo "$(date) $0 Beanstalk already installed"
#else
#    echo "$(date) $0 Checking if we need to install brew for package management"
#    if [ -e /usr/local/bin/brew ]; then
#        echo "$(date) $0 Brew is already installed"
#    else
#        echo "$(date) $0 Installing Brew"
#        ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
#        echo "$(date) $0 Installing Brew... All done"
#    fi
#
#    echo "$(date) $0 Installing beanstalk"
#    brew install beanstalkd
#    echo "$(date) $0 Installing beanstalk... done. Now setting up so that will auto start"
#    sudo cp /usr/local/opt/beanstalk/homebrew.mxcl.beanstalk.plist /Library/LaunchDaemons
#    sudo launchctl load -w /Library/LaunchDaemons/homebrew.mxcl.beanstalk.plist
#fi

echo "$(date) $0 Setting up log file: $LOG_FILE"
if [ -e $LOG_FILE ]; then
    echo "$(date) $0 Log file: $LOG_FILE already exists"
else
    sudo touch $LOG_FILE
    sudo chmod 666 $LOG_FILE
fi

if [ -e $ROTATE_CONF ]; then
    echo "$(date) $0 Rotation conf ($ROTATE_CONF) already set up"
else
    sudo touch $ROTATE_CONF
    sudo chmod g+w $ROTATE_CONF
    sudo chmod a+w $ROTATE_CONF
    echo "$LOG_FILE                       666  5     2500 *     J" >> $ROTATE_CONF
fi

echo "$(date) $0 Checking Source code installation"
if [ -e $INSTALL_DIR ]; then
    echo "$(date) $0 Source code installed, just updating the codebase"
    cd $INSTALL_DIR
    sudo ./scripts/update_codebase.sh
else
    echo "$(date) $0 First Time checkout of code"
    sudo git clone git@github.com:raxisau/JackBooted.git $INSTALL_DIR
    sudo /usr/sbin/chown -R _www:_www $INSTALL_DIR
    cd $INSTALL_DIR
    sudo $PHP ./jack.php DB:initialize
    sudo $PHP ./jack.php DB:migrate
    sudo /bin/chmod -R a+w _private
    sudo /bin/chmod -R g+w _private
    NEWVERSION=`$PHP ./jack.php Jack:version`
    echo "$(date) $0 Updated code base and migrated database to $NEWVERSION"
fi

echo "$(date) $0 Setting up crontab"
cd $INSTALL_DIR
sudo crontab -l > /tmp/tcrontab
cat ./scripts/crontab.txt | sed 's|INSTALLDIR|'$INSTALL_DIR'|g' >> /tmp/tcrontab
sudo crontab /tmp/tcrontab
rm /tmp/tcrontab
echo "$(date) $0 Crontab deployed"

echo "$(date) $0 Checking httpd.conf to ensure that the docroot set correctly and modules loaded"
DOCROOT=`grep $INSTALL_DIR /etc/apache2/httpd.conf`
if [ "$DOCROOT" == "" ]; then
    read -r -d '' NEWCONF << EOT

LoadModule php5_module libexec/apache2/libphp5.so
LoadModule rewrite_module libexec/apache2/mod_rewrite.so
DocumentRoot "$INSTALL_DIR"
<Directory "$INSTALL_DIR">
    Options FollowSymLinks Multiviews
    MultiviewsMatch Any
    AllowOverride All
    Require all granted
</Directory>

EOT

    echo "$(date) $0 Updating httpd.conf to ensure that the docroot"
    touch /tmp/httpd.conf
    while read -r line; do
        if [ "$line" = 'DocumentRoot "/Library/WebServer/Documents"' ]; then
            echo "$NEWCONF" >> /tmp/httpd.conf
        else
            echo "$line" >> /tmp/httpd.conf
        fi
    done < /etc/apache2/httpd.conf

    echo "$(date) $0 Restarting Apache"
    sudo apachectl stop
    sudo mv /etc/apache2/httpd.conf "/etc/apache2/httpd.conf.$(date).backup"
    sudo mv /tmp/httpd.conf /etc/apache2/httpd.conf
    sudo apachectl start
else
    echo "$(date) $0 httpd.conf seems to have correct DocumentRoot go to http://localhost"
    echo "$(date) $0 If you still have errors, please check this script for configuration hints"
fi

echo "$(date) $0 go to http://localhost username/password: devops@nextdc.com/password"

