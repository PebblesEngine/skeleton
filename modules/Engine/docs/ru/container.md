*Контейнер* приложения реализуется с помощью пакета [Zend-ServiceManager][]. Обычно он доступен:

* Фабрикам, зарегистрированным в *Контейнере*
* Методу `init` класса `Module`

*Контейнер* инициализируется ключем `services` *Конфигруации приложения*, состоящим из следующих элементов:

* `services` - сервисы
* `factories` - фабрики
* `invokables` - не инициализирующие фабрики
* `aliases` - псевдонимы
* `shared` - правила шаринга
* `abstract_factories` - абстрактные фабрики
* `delegators` - делегаторы
* `initializers` - инициализаторы

> Подробнее о пакете [Zend-ServiceManager][] можно прочитать в статьях: [Паттерн: Локатор служб][] и [ZendFramework: ServiceManager][].

*Конфигурация модуля* может регистрировать собственные сервисы в *Контейнере*, описывая элемент с ключем `services`:

```php
<?php
namespace MyName\MyModule;

return [
  'services' => [
    'factories' => [
      MyServiceInterface::class => MyServiceFactory::class,
      ...
    ],
  ],
  ...
];
```

[Zend-ServiceManager]: https://github.com/zendframework/zend-servicemanager
[Паттерн: Локатор служб]: https://bashka.github.io/posts/service-locator/
[ZendFramework: ServiceManager]: https://bashka.github.io/posts/zend-servicemanager/
