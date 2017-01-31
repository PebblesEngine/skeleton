<?php
namespace Pebbles\Engine\ModuleManager;

use Interop\Container\ContainerInterface;

/**
 * @author Artur Sh. Mamedbekov
 */
interface InitProviderInterface{
  /**
   * Выполняет инициализацию модуля.
   *
   * @param ContainerInterface $container
   */
  public function init(ContainerInterface $container);
}
