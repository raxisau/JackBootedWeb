<?php
if ( ! isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] != 'on' ) {
    $config['site_url']     = 'http://' . $config['server'];
}
else {
    $config['site_url']     = 'https://' . $config['server'];
}
$config['js_url']       = $config['site_url'] . '/js';
$config['images_url']   = $config['site_url'] . '/images';
$config['favicon']      = $config['site_url'] . '/favicon.ico';
$config['githubsecret'] = 'XmGPhH5wxtlc';

