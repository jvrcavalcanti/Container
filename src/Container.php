<?php

namespace Accolon\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $binds = [];
    private array $singletons = [];

    public function bind(string $id, $value)
    {
        $this->binds[$id] = $value;
    }

    public function singletons(string $id, $value)
    {
        $this->singletons[$id] = $value;
    }

    public function get(string $id)
    {
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        if (!$this->has($id)) {
            return $this->resolve($id);
        }

        $value = $this->binds[$id];

        if (is_string($value)) {
            return $this->resolve($value);
        }

        if (is_callable($value)) {
            return call_user_func($value, $this);
        }

        throw new \Exception('Id invalid!');
    }

    public function make(string $id)
    {
        return $this->get($id);
    }

    public function has($id)
    {
        return isset($this->binds[$id]);
    }

    public function resolve(string $class)
    {
        $reflector = new \ReflectionClass($class);

        if ($reflector->isInterface()) {
            throw new \ReflectionException("Interface can't instance");
        }

        $constructor = $reflector->getConstructor() ?? fn() => null;
        $params = ($constructor instanceof \ReflectionMethod) ? $constructor->getParameters() : null;

        if (is_null($params)) {
            return $reflector->newInstance();
        }

        $newParams = [];

        foreach ($params as $param) {
            if ($param->isOptional()) {
                continue;
            }

            $name = (string) $param->getType();

            if ($param->hasType() && (class_exists($name) || interface_exists($name))) {
                $newParams[] = $this->make($name);
                continue;
            }
        }

        return $reflector->newInstance(...$newParams);
    }
}
