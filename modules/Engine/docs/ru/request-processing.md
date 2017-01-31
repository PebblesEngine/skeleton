После обращения клиента к файлу `index.php`, выполняется следующая последовательность обработки, нацеленная на формирование ответа:

![Модель обработки запроса](https://www.plantuml.com/plantuml/png/NP0n3i8m34Ntd283GpAK0p0WeSvTu0IbCTHIweJ40OvF0n5HEbdwVlzPycbY5sHl4OZ5RyZkA94ClFT-zl8W0pmIH1Orsm18oBqZ7f4WgDtYJB8Gr-I6TztMpYunXBMpZ3pDggYrA7ctFPtZ8QEjvdzjPQu9hHdG60klCO2g7IqgjpNNGck-G7X-jHOZ0wdAQL5sMLK-bPyJlQC87m00 "Модель обработки запроса")

# Создание контейнера
Начальным этапом является сбор конфигурации приложения и создание контейнера.

## Сбор конфигурации
*Конфигурация приложения* представляет простой массив, собранный из:

1. Конфигурации дистрибутива
2. Конфигурации модулей

*Конфигурация дистрибутива* запрашивается из каталога `config` в виде двух файлов:

* `global.php` - неизменяемые конфигурации дистрибутива
* `local.php` - конфигурации дистрибутива для конкретной установки (изначально отсутствуют)

Именно эти конфигурации должны содержать информацию обо всех доступных приложению *Модулях* в виде элемента с ключем `modules`:

```php
<?php
return [
  // Модули приложения
  'modules' => [
    'Pebbles\Engine',
    ...
  ],
];
```

*Конфигурация модулей* запрашивается через вызов метода `getConfig` класса  `Module` у каждого доступного приложению *Модуля*:

```php
<?php
namespace Pebbles\Engine;

use Pebbles\Engine\ModuleManager\ConfigProviderInterface;

class Module implements ConfigProviderInterface{
  public function getConfig(){
    // Конфигурация модуля
    return [
      ...
    ];
  }
}
```

## Инициализация контейнера
После сборки *Конфигурации приложения*, выполняется процесс создания и инициализации *Контейнера*. В качестве контейнера используется класс `ServiceManager` пакета [Zend-ServiceManager][], а для его инициализации используется ключ `services` *Конфигурации приложения*. Исходный массив *Конфигурации приложения* доступен в контейнере под именем `Configuration`.

## Инициализация модулей
После создания *Контейнера* последовательно вызывается метод `init` для класса `Module` каждого доступного приложению *Модуля*.

# Роутинг запроса
Следующим этапом, является получение объекта *Запроса*, в виде экземпляра `ServerRequest` пакета [Zend-Diactoros][] и его роутинг. Для этого используются *Правила роутинга* пакета [Bricks-HttpRoutingPsr][], которые должны быть сформированы из *Контейнера*.

Для создания *Правил роутинга* используется фабрика `Pebbles\Engine\Service\Factory\RouterFactory`, которая, в свою очередь, ориентируется на ключ `routing` *Конфигурации приложения*:

```php
<?php
namespace Pebbles\Engine;

use Bricks\Http\RoutingPsr\Rule as RoutingRule;

return [
  ...
  'routing' => [
    // Роутинг главной страницы сайта
    new RoutingRule\PathLiteralRule('/', [
      // Очередь обработки
      'middleware' => [
        ...
      ],
    ]),
    new RoutingRule\PathRegexRule('~^/(?<controller>\w+)(\/(?<action>\w+))?~', [
      'middleware' => [
        ...
      ],
    ]),
  ],
];
```

> Все перечисленные правила роутинга по умолчанию объединяются в экземпляре класса `Bricks\Http\RoutingRule\Rule\Logic\OrRule`.

С помощью полученного *Правила роутинга* выполняется роутинг *Запроса*. В итоге ожидается получить экземпляр, удовлетворяющий интерфейсу `Bricks\Http\RoutingRule\RouteMatchInterface`, называемый *Результатом роутинга*.

# Формирование Middleware
*Результат роутинга* используется для формирования очереди обработки с помощью `Pebbles\Engine\Service\Factory\MiddlewareFactory`. Данная фабрика использует параметр `middleware` для создания очереди `Bricks\Middleware\MiddlewareQueue`. Элементами этой очереди являются *Обработчики*, реализующие интерфейс `Bricks\Middleware\MiddlewareInterface`:

```php
<?php
namespace Pebbles\Engine;

use Bricks\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class IndexMiddleware implements MiddlewareInterface{
  ...
  public function __invoke(Request $request, Response $response, MiddlewareInterface $next = null){
    $id = (int) $request->getParms()['id'];
    $article = $this->articleTable->find($id);

    $response->getBody()->write($this->template('view/article/view', ['item' => $article]));

    return $response;
  }
}
```

# Обработка запроса и возврат результата
Каждый экземпляр *Обработчика* очереди вызывается последовательно для формирования *Ответа* клиенту. *Обработчик* может как изменять или возвращать экземпляр `Psr\Http\Message\ResponseInterface`, что повлияет на *Ответ*, так и вообще не взаимодействовать с ним, а только обновлять состояние приложения как реакцию на *Запрос*.

[Zend-ServiceManager]: https://github.com/zendframework/zend-servicemanager
[Zend-Diactoros]: https://github.com/zendframework/zend-diactoros
[Bricks-HttpRoutingPsr]: https://github.com/Bashka/bricks_http_routingpsr
