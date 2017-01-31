<?php
namespace Pebbles\Engine\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Server;

/**
 * @author Artur Sh. Mamedbekov
 */
class ServerFactory implements FactoryInterface{
  /**
   * {@inheritdoc}
   */
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
    $server = Server::createServerFromRequest($options['middleware'], $options['request']);
    $server->setEmitter($options['emitter']);
    return $server;
  }
}
