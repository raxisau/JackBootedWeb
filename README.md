## Welcome to JackBooted PHP Framework

I have given up on MVC!

I needed a reboot on PHP development!

I needed something that was Jacked for Speed and reduced overhead!

I have done some projects using Laravel and I am not impressed. Oh yes, it is good framework. but OMG the overhead!

JackBooted is yet another framework.
* It is PHP rebooted back to it's roots.
* It is PHP Jacked in speed, and convenience without the massive overhead.
* It is a single page app, or not. Up to you, or mix!
* It is OO
* It is all of the strength of Laravel without the overhead.
* It is MC. So you have models and controllers (WebPage class) that generates HTML. There is a templating, but I rarely need it
* It has Object Relational Mapping that makes sense. Use it or access the database directly
* It has Helper objects for all common web development operations.
* It has Protection from URL/Form Tampering. CSRF protection, Time based tampering. Everything that [OWASP](https://www.owasp.org/index.php/Main_Page) teaches us to fear.
* It has an autoloader that makes sense. Implements Static initialisation that makes sense. (Called in Autoloader)
* It has a CRUD that works with a single statement (CRUD::factory('tblName')->index()). Crud handles Pagination, Sorting
* It does migrations.
* It deploys easily to cPanel web hosting
* It has built in scheduling
* Built in User authentication and access levels, and WebPage Access control
* Auto-generated Wordpress style configuration if you want it. (Config::get( $key, $default ) )
* It is easy to change and edit.

if you have any interest in this contact me jack@brettdutton.com

## Installation
There is a file, scripts/deploy_maxOSX.sh, that will give you all the steps that you need to install the example application. Here are the main steps

    WEB_DIR=/Library/WebServer/Documents
    INSTALL_DIR=$WEB_DIR/jack
    sudo git clone git@github.com:raxisau/JackBooted.git $INSTALL_DIR
    sudo chown -R _www:_www $INSTALL_DIR
    cd $INSTALL_DIR
    sudo php ./jack.php DB:initialize
    sudo php ./jack.php DB:migrate
    sudo chmod -R a+w _private
    sudo chmod -R g+w _private
    php ./jack.php JACK:version

You will also need to make sure that you have your httpd.conf correctly set up.

Search for the line

    DocumentRoot "/Library/WebServer/Documents"

Replace with

    LoadModule php5_module libexec/apache2/libphp5.so
    LoadModule rewrite_module libexec/apache2/mod_rewrite.so
    DocumentRoot "/Library/WebServer/Documents/jack"
    <Directory "/Library/WebServer/Documents/jack">
        Options FollowSymLinks Multiviews
        MultiviewsMatch Any
        AllowOverride All
        Require all granted
    </Directory>

example of the site here: http://www.brettdutton.com/jack/index.php u/p: jack@brettdutton.com/password
