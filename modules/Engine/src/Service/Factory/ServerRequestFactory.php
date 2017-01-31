<?php
namespace Pebbles\Engine\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\ServerRequestFactory as DiactorosServerRequestFactory;

/**
 * @author Artur Sh. Mamedbekov
 */
class ServerRequestFactory implements FactoryInterface{
  /**
   * {@inheritdoc}
   */
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
    return DiactorosServerRequestFactory::fromGlobals();
  }
}
