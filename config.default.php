<?php
/**
 * Don't change this file
 */
$config['version']      = 'JACKBOOTWEB Version 13.1';
$config['cookie_path']  = '/';
$config['LF']           = "\r\n";

// Pick up the current server config
$config['site_path']    = __DIR__;
$config['tmp_path']     = $config['site_path'] . '/_private';
$config['class_path']   = [ $config['site_path'] . '/vendor', $config['site_path'] . '/app' ];

$config['server']       = ( isset ( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['HTTP_HOST'] : 'cli.local';
$config['site_url']     = 'http://' . $config['server'];
$config['js_url']       = $config['site_url'] . '/js';
$config['images_url']   = $config['site_url'] . '/images';
$config['favicon']      = $config['site_url'] . '/favicon.ico';

$config['local-driver'] = 'sqlite';
$config['local-host']   = $config['tmp_path'] . '/jackbooted.sqlite';
$config['local-db']     = '';
$config['local-user']   = '';
$config['local-pass']   = '';

$config['boss']         = 'brett@brettdutton.com';
$config['mail.smtp']    = 'www.brettdutton.com';
$config['desc']         = 'Jackbooted Example Site';
$config['title']        = 'Jackbooted Example Site Title';

$config['check_priviliages'] = true;   // If true checks all actions agains privilages tables
$config['encrypt_override']  = false;  // If this is set to true, the system does not do encryption
$config['maintenance']       = false;  // If this is set to true the system redirects to the maintenance.php page
$config['save_cookies']      = true;   // If true then the username, and password are saved in cookies user will have to login more often, but less secure
$config['jb_self_register']  = false;  // If true then guest user will be able to create account
$config['jb_forgery_check']  = true;   // If true system will check for URL and form variable tampering
$config['jb_tamper_detail']  = true;   // If true there will be more details about Tampering violations
$config['jb_audit_tables']   = false;  // If true all models will audit the tables to ensure they exist.
$config['jb_db']             = true;   // If this is standard Jackbooted database then the tables are of a vertain format

$config['timezone']          = 'UTC';
$config['known']             = [ ]; //TamperGuard Variables. Variables that add to Tamperguard that are not checked

// Jackbooted checks for Timeout of URL, also checks for tampering. These variables must
// exist in the url or form variables
// The list below are the scripts that are exempt from checking.
$config['exempt']            = [ 'cron.php', 'router.php', 'menu.php' ]; // List of files that are not checked

$config['crypto_location']   = 'session';   // If this is 'session' then it will look to the session variables, otherwise use the crypto_key
$config['crypto_key']        = 'PredefinedEncryptionKeyhgfqf786w7676wedw'; // This key is shuffled around and put into the session
                                                                           // Which is then used for encrypting form variables
                                                                           // You probably should not use this in your app as changing it
                                                                           // will break anything that uses it. eg save encryption to DB
                                                                           // and then change will not be able to unencrypt
