<?php
namespace Pebbles\Engine\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Bricks\Http\RoutingPsr\RouteRuleInterface;
use Bricks\Http\RoutingPsr\Rule\Logic\OrRule;
use Bricks\Http\RoutingPsr\Rule\NullRule;

/**
 * @author Artur Sh. Mamedbekov
 */
class RouterFactory implements FactoryInterface{
  /**
   * {@inheritdoc}
   */
  public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
    $config = $container->get('Configuration');
    $router = isset($config['routing'])? $config['routing'] : new NullRule;
    if(is_array($router)){
      $router = new OrRule($router);
    }
    elseif(!$router instanceof RouteRuleInterface){
      $router = new NullRule;
    }

    return $router;
  }
}
