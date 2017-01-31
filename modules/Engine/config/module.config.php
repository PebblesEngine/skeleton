<?php
namespace Pebbles\Engine;

use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Psr\Http\Message\ServerRequestInterface;
use Pebbles\Engine\Service\Factory\ServerRequestFactory;
use Bricks\Http\RoutingPsr\RouteRuleInterface;
use Pebbles\Engine\Service\Factory\RouterFactory;
use Zend\Diactoros\Server;
use Pebbles\Engine\Service\Factory\ServerFactory;
use Bricks\Middleware\MiddlewareInterface;
use Pebbles\Engine\Service\Factory\MiddlewareFactory;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;

return [
  'services' => [
    'invokables' => [
      EventManagerInterface::class => EventManager::class,
      EmitterInterface::class => SapiEmitter::class,
    ],
    'factories' => [
      ServerRequestInterface::class => ServerRequestFactory::class,
      RouteRuleInterface::class => RouterFactory::class,
      Server::class => ServerFactory::class,
      MiddlewareInterface::class => MiddlewareFactory::class,
    ],
  ],
  'routing' => [
  ],
];
