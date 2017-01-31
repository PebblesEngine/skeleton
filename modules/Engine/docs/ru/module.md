Архитектура `PebblesEngine` позволяет расширить функциональность системы за счет *Модулей*. Для реализации собственного модуля достаточно зарегистрировать его в конфигурационном файле `config/local.php` под элементом с ключем 'modules'. Если модуль будет доступен для автозагрузки, он будет подключен к приложению.

# Пример создания собственного модуля
В качестве примера, рассмотрим процесс создание собственного модуля с простейшей функциональностью - генерация статичных HTML страниц по литеральным URL-адерсам. Для этого создадим в каталоге `modules` подкаталог и назовем его `MyLanding`. Это корневой каталог нашего модуля и он должен содержать весь исходный код, файлы шаблонов, тесты и конфигурацию модуля. 

Для начала займемся конфигурацией модуля, создав файл `modules/MyLanding/config/module.config.php` со следующим содержимым:

```php
<?php
namespace Pebbles\MyLanding;

use Bricks\Http\RoutingPsr\Rule\PathLiteralRule;

return [
  // Правила роутинга модуля
  'routing' => [
    // Главная страница
    new PathLiteralRule('/', [
      'middleware' => [
        Middleware\IndexMiddleware::class, // Обработчик главной страницы
      ],
    ]),
  ],
];
```

На данный момент конфигурация представляет собой объявление одного правила роутинга для главной страницы сайта. *Обработчиком*, генерирующим HTML разметку для этой страницы будет `Pebbles\MyLanding\Middleware\IndexMiddleware`, который мы создадим позже.

После создания файла конфигурации, необходимо предоставить его системе. Для этого создайте класс `modules/MyLanding/src/Module.php` вида:

```php
<?php
namespace Pebbles\MyLanding;

use Pebbles\Engine\ModuleManager\ConfigProviderInterface;

// Интерфейс ConfigProviderInterface сообщает, что модуль имеет собственную конфигурацию
class Module implements ConfigProviderInterface{
  // Метод должен предоставлять конфигурацию модуля системе в виде массива
  public function getConfig(){
    // В качестве конфигурации используется массив, объявленный в файле module.config.php
    return include(__DIR__ . '/../config/module.config.php');
  }
}
```

При старте системы, конфигурации всех модулей будут собраны и обработаны. Таким образом правила роутинга нашего модуля попадут в общую конфигурацию приложения и будут ее частью.

Далее следует создать *Обработчик*, о котором мы говорили выше. Для этого создадим класс `modules/MyLanding/src/Middleware/IndexMiddleware.php` вида:

```php
<?php
namespace Pebbles\MyLanding\Middleware;

use Bricks\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Класс обработчика
class IndexMiddleware implements MiddlewareInterface{
  // Метод должен обработать запрос и предоставить ответ, записав в него любые данные
  public function __invoke(Request $request, Response $response, MiddlewareInterface $next = null){
    // Эта разметка будет выведена на главной странице сайта
    $response->getBody()->write('<h1>Main page</h1>');
    return $response;
  }
}
```

Следующим шагом необходимо сообщить о способе создания *Обработчика*. Для этого вернемся к файлу конфигурации модуля `modules/MyLanding/config/module.config.php` и добавим в него раздел `services`:

```php
<?php
namespace Pebbles\MyLanding;

use Bricks\Http\RoutingPsr\Rule\PathLiteralRule;

return [
  'services' => [
    // Обработчик не имеет зависимостей, потому он создается как Invokable-сервис
    'invokables' => [
      Middleware\IndexMiddleware::class => Middleware\IndexMiddleware::class,
    ],
  ],
  'routing' => [
    ...
  ],
];
```

В результате у нас получится модуль со следующей файловой структурой:

```bash
modules/
  MyLanding/
    config/
      module.config.php
    src/
      Middleware/
        IndexMiddleware.php
      Module.php
```

Теперь зарегистрируем модуль в файле `config/local.php`:

```php
<?php
return [
  'modules' => [
    'Pebbles\MyLanding',
  ],
];
```

Для простейшего модуля, ответственного за вывод главной страницы сайта этого достаточно. Теперь необходимо обеспечить автозагрузку модуля. Для этого добавим информацию о расположении модуля в `composer.json`:

```js
{
    ...
	"autoload": {
		"psr-4": {
			"Pebbles\\Engine\\": "modules/Engine/src/",
			"Pebbles\\MyLanding\\": "modules/MyLanding/src/"
		}
	}
    ...
}
```

И выполним следующую команду в консоли для генерации нового файла автозагрузки:

```bash
composer dump-autoload
```

Модуль готов к использованию. Запустите web-сервер с помощью следующей команды и перейтиде по адресу `localhost:8000` в браузере:

```bash
php -S 127.0.0.1:8000 index.php
```

# Пример установки стороннего модуля
Если вы хотите установить в систему сторонний модуль, на пример [Pebbles\MyLanding-Module][], то необходимо объявить его в качестве зависимости в файле `composer.json`:

```js
{
    ...
	"require": {
		"php": ">= 5.4 | ^7.0",
		"zendframework/zend-servicemanager": "^3.0",
		"zendframework/zend-eventmanager": "^3.0",
		"zendframework/zend-diactoros": "^1.0",
		"bashka/bricks_http_routingpsr": "^1.0",
		"bashka/bricks_middleware": "^1.0",
        "pebblesengine/mylanding-module": "^1.0"
	},
    ...
}
```

Далее выполним следующую команду в консоли, для установки модуля:

```bash
composer update
```

Теперь зарегистрируем модуль в файле `config/local.php`:

```php
<?php
return [
  'modules' => [
    'Pebbles\MyLanding',
  ],
];
```

Модуль готов к использованию. Запустите web-сервер с помощью следующей команды и перейтиде по адресу `localhost:8000` в браузере:

```bash
php -S 127.0.0.1:8000 index.php
```

[Pebbles\MyLanding-Module]: https://github.com/PebblesEngine/mylanding-module
