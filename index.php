<?php
chdir(__DIR__);
require(__DIR__ . '/vendor/autoload.php');

use Pebbles\Engine\ModuleManager\ModuleManager;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Bricks\Http\RoutingPsr\RouteRuleInterface;
use Bricks\Http\RoutingPsr\RouteMatchInterface;
use Bricks\Middleware\MiddlewareInterface;
use Zend\Diactoros\Server;
use Zend\Diactoros\Response\EmitterInterface;

// Create module manager
$config = array_merge_recursive(
  include(__DIR__ . '/config/global.php'),
  include(__DIR__ . '/config/local.php')
);
$moduleManager = new ModuleManager($config['modules']);

// Get modules configuration
$config = array_merge_recursive($config, $moduleManager->getConfig());

// Init container
$container = new ServiceManager($config['services']);
$container->setService('Configuration', $config);

// Init event manager
$eventManager = $container->get(EventManagerInterface::class);
$eventManager->trigger('Pebbles\Engine::start');

// Init modules
$moduleManager->init($container);

// Route
$request = $container->get(ServerRequestInterface::class);
$router = $container->get(RouteRuleInterface::class);
$routeMatch = $router->match($request);

if(is_null($routeMatch)){
  $routeMatch = $eventManager->triggerUntil(function($result){
    return $result instanceof RouteMatchInterface;
  }, 'Pebbles\Engine::router.404')->last();
  if(!$routeMatch instanceof RouteMatchInterface){
    exit;
  }
}
$eventManager->trigger('Pebbles\Engine::router.200', [
  'routeMatch' => $routeMatch,
]);

// Get middleware queue
$middleware = $container->build(MiddlewareInterface::class, [
  'routeMatch' => $routeMatch,
  'middleware' => $routeMatch->getParam('middleware'),
]);

// Get server
$emitter = $container->get(EmitterInterface::class);
$server = $container->build(Server::class, [
  'middleware' => $middleware,
  'request' => $request,
  'emitter' => $emitter,
]);

// Listen server
$eventManager->trigger('Pebbles\Engine::controller.call.after', [
  'server' => $server,
]);
$server->listen();
$eventManager->trigger('Pebbles\Engine::controller.call.before', [
  'server' => $server,
]);

$eventManager->trigger('Pebbles\Engine::end');
