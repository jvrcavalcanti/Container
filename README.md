```php
<?php

use Accolon\Container\Container;

require_once './vendor/autoload.php';

$app = new Container();

$stdClass = new stdClass();

$stdClass->name = "George";

$app->singletons("class", $stdClass);

$app->bind("class2", \stdClass::class);

$app->make('class')->name = "George2";

var_dump($app->make('class'));
var_dump($app->make('class2'));
```