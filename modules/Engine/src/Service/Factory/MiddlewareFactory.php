<?php
namespace Pebbles\Engine\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Bricks\Middleware\MiddlewareInterface;
use Bricks\Middleware\MiddlewareQueue;

/**
 * @author Artur Sh. Mamedbekov
 */
class MiddlewareFactory implements FactoryInterface{
  /**
   * {@inheritdoc}
   */
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
    $queue = new MiddlewareQueue;
    $middlewareOption = $options['middleware'];
    foreach($middlewareOption as $middleware => $option){
      if(is_int($middleware)){
        $middleware = $option;
        $option = [];
      }
      if(!$middleware instanceof MiddlewareInterface){
        $middleware = $container->build($middleware, $option);
      }
      $queue->pipe($middleware);
    }

    return $queue;
  }
}
