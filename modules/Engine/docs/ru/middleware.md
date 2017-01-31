Логика преобразования *Запроса* клиента в *Ответ* должна быть представлена в виде очереди *Обработчиков* - классов, реализующих интерфейс `Bricks\Middleware\MiddlewareInterface` пакета [Bricks-Middleware][]. Для этого достаточно реализовать единственный метод `__invoke`, принимающий в качестве первого аргумента экземпляр *Запроса*, а в качестве второго - экземпляр *Ответа*. Третим аргументом может быть передан следующий *Обработчик*, которому данный может делегировать управление.

Метод `__invoke` может выбрать один из двух вариантов обработки запроса:

1. Сформировать *Ответ* (в виде экземпляра класса, реализующего интерфейс `Psr\Http\Message\ResponseInterface` пакета [Psr-HttpMessage][]) самостоятельно и вернуть его с помощью `return`. В этом случае обработка запроса очередью прекратится, а клиенту будет возвращен полученный из *Обработчика* *Ответ*
2. Обработать *Запрос*, возможно даже изменить *Ответ*, но делегировать дальнейшую обработку следующему *Обработчику* вызвав третий параметр

Пример класса *Обработчика*, возвращающего *Ответ*:

```php
<?php
namespace MyName\MyModule\Middleware;

use Bricks\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Zend\Diactoros\Response\JsonResponse;

class ArticleApiMiddleware implements MiddlewareInterface{
  public function __invoke(Request $request, Response $response, MiddlewareInterface $next = null){
    ...

    // Возврат ответа клиенту в виде JSON
    return new JsonResponse([
      'title' => $article->getTitle(),
      'content' => $article->getContent(),
    ]);
  }
}
```

Пример обработчика, делегирующего обработку:

```php
<?php
namespace MyName\MyModule\Middleware;

use Bricks\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthMiddleware implements MiddlewareInterface{
  public function __invoke(Request $request, Response $response, MiddlewareInterface $next = null){
    $cookies = $request->getCookieParams();
    // Остановка очереди, если пользователь не аутентифицирован
    if(!isset($cookies['SID'])){
      $response = $response->withStatus(403);
      $response->getBody()->write('403: Access denied');

      return $response; // Без вызова $next, дальнейшая обработка очереди завершается
    }

    // Делегирование обработки
    return $next($request, $response);
  }
}
```

Очередь обработки для конкретного *Запроса* определяется *Правилами роутинга*, а именно параметром `middleware` *Результата роутинга*:

```php
<?php
namespace MyName\MyModule;

use Bricks\Middleware\DelegateMiddleware;
use Bricks\Http\RoutingPsr\Rule\PathLiteralRule;

return [
  'routing' => [
    new PathLiteralRule('/', [
      // Очередь обработки запроса к главной странице сайта
      'middleware' => [
        ...
        // Делегирующий обработчик передает управление методу indexAction класса IndexController
        DelegateMiddleware::class => [
          [Controller\IndexController::class, 'indexAction']
        ],
        ...
      ],
    ]),
  ],
];
```

> Очередь обработки параметра `middleware` может не ограничиваться одним *Обработчиком*, а включать любое их количество. Они будут вызываться последовательно до формирования *Ответа*.

[Bricks-Middleware]: https://github.com/Bashka/bricks_middleware
[Psr-HttpMessage]: https://github.com/php-fig/http-message
