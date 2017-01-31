<?php
namespace Pebbles\Engine;

use Pebbles\Engine\ModuleManager\ConfigProviderInterface;

/**
 * @author Artur Sh. Mamedbekov
 */
class Module implements ConfigProviderInterface{
  /**
   * {@inheritdoc}
   */
  public function getConfig(){
    return include(__DIR__ . '/../config/module.config.php');
  }
}
