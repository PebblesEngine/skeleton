<?php
namespace Pebbles\Engine\ModuleManager;

use Interop\Container\ContainerInterface;

/**
 * Инициатор модулей.
 *
 * @author Artur Sh. Mamedbekov
 */
class ModuleManager{
  /**
   * @var object[] Модули приложения.
   */
  protected $modules;

  protected function loadModules(array $moduleNames){
    foreach($moduleNames as $moduleName){
      $moduleClassName = $moduleName . '\Module';
      if(!class_exists($moduleClassName)){
        continue;
      }
      $this->modules[$moduleName] = new $moduleClassName;
    }
  }

  /**
   * @param array $moduleNames Имена инициируемых модулей.
   */
  public function __construct(array $moduleNames){
    $this->loadModules($moduleNames);
  }

  /**
   * @return array Конфигурация модулей.
   */
  public function getConfig(){
    return array_reduce($this->modules, function($carry, $item){
      $itemConfig = ($item instanceof ConfigProviderInterface)? $item->getConfig() : [];

      if(is_null($carry)){
        return $itemConfig;
      }

      return array_merge_recursive($carry, $itemConfig);
    });
  }

  /**
   * Выполняет инициализацию модулей.
   */
  public function init(ContainerInterface $container){
    foreach($this->modules as $module){
      if(!$module instanceof InitProviderInterface){
        continue;
      }
      $module->init($container);
    }
  }

  /**
   * @return array Все загруженные модули.
   */
  public function getModules(){
    return $this->modules;
  }
}
