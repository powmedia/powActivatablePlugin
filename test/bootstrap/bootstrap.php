<?php

//TODO: Remove hardcoded link to symfony lib
if (!isset($_SERVER['SYMFONY']))
{
    $_SERVER['SYMFONY'] = '/Users/charlie/Sites/_lib/symfony-1.4/lib';
}

if (!isset($app))
{
  $app = 'frontend';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);

require_once 'PHPUnit/Framework.php';

new sfDatabaseManager($configuration);
