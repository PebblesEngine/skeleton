<?php
namespace Pebbles\Engine\ModuleManager;

/**
 * @author Artur Sh. Mamedbekov
 */
interface ConfigProviderInterface{
  /**
   * @return array Конфигурация модуля.
   */
  public function getConfig();
}
