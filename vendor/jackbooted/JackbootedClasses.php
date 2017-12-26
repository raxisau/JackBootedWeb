<?php
namespace Jackbooted;
/**
 * @copyright Confidential and copyright (c) 2018 Jackbooted Software. All rights reserved.
 *
 * Written by Brett Dutton of Jackbooted Software
 * brett at brettdutton dot com
 *
 * This software is written and distributed under the GNU General Public
 * License which means that its source code is freely-distributed and
 * available to the general public.
 */
/*
use \Jackbooted\Admin\Admin;
use \Jackbooted\Admin\FancyLogin;
use \Jackbooted\Admin\ImagePositionLocator;
use \Jackbooted\Admin\Login;
use \Jackbooted\Admin\SuperAdmin;
use \Jackbooted\Config\AdminConfig;
use \Jackbooted\Config\Cfg;
use \Jackbooted\Config\Config;
use \Jackbooted\Config\PreferenceLoader;
use \Jackbooted\Config\Preferences;
use \Jackbooted\Cron\Cron;
use \Jackbooted\Cron\CronDAO;
use \Jackbooted\Cron\CronManager;
use \Jackbooted\Cron\CronParser;
use \Jackbooted\Cron\Scheduler;
use \Jackbooted\Cron\SchedulerDAO;
use \Jackbooted\Cron\SchedulerManager;
use \Jackbooted\DB\DAO;
use \Jackbooted\DB\DB;
use \Jackbooted\DB\DBMaintenance;
use \Jackbooted\DB\DBManager;
use \Jackbooted\DB\DBTable;
use \Jackbooted\DB\MDB;
use \Jackbooted\DB\ORM;
use \Jackbooted\Forms\Columnator;
use \Jackbooted\Forms\CRUD;
use \Jackbooted\Forms\Grid;
use \Jackbooted\Forms\Navigator;
use \Jackbooted\Forms\Paginator;
use \Jackbooted\Forms\PipeLine;
use \Jackbooted\Forms\Request;
use \Jackbooted\Forms\Response;
use \Jackbooted\G;
use \Jackbooted\Html\GoogleChartAPI;
use \Jackbooted\Html\Gravatar;
use \Jackbooted\Html\JS;
use \Jackbooted\Html\Lists;
use \Jackbooted\Html\Tag;
use \Jackbooted\Html\Template;
use \Jackbooted\Html\Validator;
use \Jackbooted\Html\WebPage;
use \Jackbooted\Html\Widget;
use \Jackbooted\Mail\GMailSender;
use \Jackbooted\Mail\Mailer;
use \Jackbooted\Mail\POP3Class;
use \Jackbooted\Mail\SMTPSendMail;
use \Jackbooted\Security\Captcha;
use \Jackbooted\Security\Cryptography;
use \Jackbooted\Security\CSRFGuard;
use \Jackbooted\Security\Privileges;
use \Jackbooted\Security\TamperGuard;
use \Jackbooted\Security\TimeGuard;
use \Jackbooted\Time\Stopwatch;
use \Jackbooted\Util\AutoLoader;
use \Jackbooted\Util\ClassLocator;
use \Jackbooted\Util\Cookie;
use \Jackbooted\Util\CSV;
use \Jackbooted\Util\DataCache;
use \Jackbooted\Util\DeployChangeset;
use \Jackbooted\Util\Invocation;
use \Jackbooted\Util\JB;
use \Jackbooted\Util\Log4PHP;
use \Jackbooted\Util\MenuUtils;
use \Jackbooted\Util\Module;
use \Jackbooted\Util\ObjectUtil;
use \Jackbooted\Util\PDFUtil;
use \Jackbooted\Util\PHPExt;
use \Jackbooted\Util\Sess;
use \Jackbooted\Util\StringUtil;
use \Jackbooted\Util\XLS;
*/