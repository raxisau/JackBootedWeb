<?php
use \Jackbooted\Config\Cfg;
use \Jackbooted\Html\JS;

require_once ( __DIR__ . '/config.php' );
$pageTimer = new \Jackbooted\Time\Stopwatch( "Page Load" );
?>
<html>
    <head>
        <title><?= Cfg::get( 'desc' ) ?></title>
        <meta name="description" content="<?= Cfg::get( 'desc' ) ?>" />
        <meta name="keywords"    content="<?= Cfg::get( 'desc' ) ?>" />
        <link type="text/css" rel="stylesheet" media="screen" href="style.css" />
        <?php /* <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" > */ ?>
        <?php /* <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css"> */ ?>
        <?= JS::libraryWithDependancies( JS::JQUERY ); ?>
    </head>
    <body>
        <table width="100%" cellpadding="5" cellspacing="0" border="1">
            <tr>
                <td width="100%">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td nowrap="nowrap" valign="top" height="50" width="100">
                                <a href="?"><img alt="ONEDC Appliance Logo" src="<?= \App\Jack::logo(); ?>" border=0 height="90"/></a>
                            </td>
                            <td nowrap="nowrap" valign="top" align="left">
                                <h1>Jackbooted</h1>
                                <br/><br/>
                            </td>
                            <td nowrap="nowrap" valign="top" align="right" width="100%">
<?= \Jackbooted\Admin\FancyLogin::controller(); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="600px" width="100%">
                    <table width="100%" height="100%" cellpadding="5" cellspacing="0">
                        <tr>
                            <td nowrap="nowrap" valign="top">
<?= ( \Jackbooted\G::isLoggedIn() ) ? \Jackbooted\Util\MenuUtils::display() : '&nbsp;'; ?>
                            </td>
                            <td align="left" valign="top" width="100%">
<?= \Jackbooted\Html\WebPage::controller( Cfg::get( 'def_display' ) ); ?>
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
                                &copy; <?= date( 'Y' ) ?>.
                                Created by <a href="<?= Cfg::get( 'site_url' ) ?>"><?= Cfg::get( 'title' ) ?></a>
<?= Cfg::get( 'copyright' ); ?>
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
