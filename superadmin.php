<?php
use \Jackbooted\Admin\Admin;
use \Jackbooted\Admin\Login;
use \Jackbooted\Config\Cfg;
use \Jackbooted\DB\DBManager;
use \Jackbooted\Admin\SuperAdmin;

require_once dirname ( __FILE__ ) . '/config.php';
Cfg::set ( 'accesslevel', 0 );
$pageTimer = new \Jackbooted\Time\Stopwatch ( 'Page Load' );
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html>
    <head>
        <meta name="description" content="Super Administration" />
        <meta name="keywords"    content="Super Administration" />
        <title>Super Administration</title>
        <link rel="shortcut icon" href="<?= Cfg::get ( 'favicon' ) ?>" type="image/x-icon">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" >
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    </head>
    <body>
        <table width="100%" cellpadding="5" cellspacing="0" class="table table-bordered">
            <tr>
                <td width="100%">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="100" nowrap="nowrap" valign="top" height="50">
                                <a href="?"><img alt="Logo" src="<?= \App\Jack::logo (); ?>" border=0 height="90"/>
                            </td>
                            <td width="100%" nowrap="nowrap" valign="top" align="left">
                                <h1>Super Administration</h1>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="600px" width="100%">
                    <table class="table table-bordered">
                        <tr>
                            <td nowrap="nowrap" valign="top">
                                <?= SuperAdmin::menu () . "\n".
                                    Admin::menu () .      "\n".
                                    Login::menu () .      "\n".
                                    DBManager::menu (); ?>
                            </td>
                            <td align="left" valign="top" width="100%">
                                <?= \Jackbooted\Html\WebPage::controller ( SuperAdmin::DEF ); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="100%">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td nowrap="nowrap" align="left"><?= Cfg::get( 'build_version' ) ?></td>
                            <td width="100%"    align="center">
                                &copy; <?= date('Y') ?>.
                                Created by <a href="<?= Cfg::get( 'site_url' ) ?>"><?= Cfg::get( 'title' ) ?></a>
                                <?= Cfg::get ( 'copyright' ); ?>
                            </td>
                            <td nowrap="nowrap" align="right">
                                <?= $pageTimer->logLoadTime(); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php /* <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script> */ ?>
    </body>
</html>
