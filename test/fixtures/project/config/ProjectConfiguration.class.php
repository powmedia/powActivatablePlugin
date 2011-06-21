<?php

//TODO: Remove hardcoded link to symfony lib
if (!isset($_SERVER['SYMFONY']))
{
    $_SERVER['SYMFONY'] = '/Users/charlie/Sites/_lib/symfony-1.4/lib';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfDoctrinePlugin');
    $this->enablePlugins('powActivatablePlugin');
    $this->setPluginPath('sfDoctrinePlugin', $_SERVER['SYMFONY'].'/plugins/sfDoctrinePlugin');
    $this->setPluginPath('powActivatablePlugin', dirname(__FILE__).'/../../../..');
  }
}
