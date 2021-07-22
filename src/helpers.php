<?php

use Accolon\Container\Container;

if (!function_exists('class_or_interface_exists')) {
    function class_or_interface_exists(string $name)
    {
        return class_exists($name) || interface_exists($name);
    }
}

if (!function_exists('container')) {
    function container(): Container
    {
        static $container = null;

        if (!$container) {
            $container = new Container;
        }
        
        return $container;
    }
}

if (!function_exists('resolve')) {
    function resolve(string|array|\Closure $action, ?Container $container = null)
    {
        if (!$container) {
            $container = container();
        }

        if (is_string($action) && class_or_interface_exists($action)) {
            return $container->get($action);
        }

        return $container->make($action);
    }
}
