<?php
namespace App\Commands;

class MigrationsCLI extends \Jackbooted\Html\WebPage
{
    public static function init () {
        self::$log = \Jackbooted\Util\Log4PHP::logFactory ( __CLASS__ );
    }

//    This is the template of a migration
//    Reflection picks up all the methods that start with migrate    
//    public static function migrate2015_10_03_00_00()
//    {
//        echo __METHOD__;
//    }
}
