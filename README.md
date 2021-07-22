Use get for objects and get for rest.

```php
<?php

use Accolon\Container\Container;

require_once './vendor/autoload.php';

$container = new Container();

$stdClass = new stdClass();

$stdClass->name = "George";

$container->singletons("class", $stdClass);

$container->bind("class2", \stdClass::class);

$container->get('class')->name = "George2";

var_dump($container->get('class'));
var_dump($container->get('class2'));
```