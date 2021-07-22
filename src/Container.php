<?php

namespace Accolon\Container;

use Accolon\Container\Exceptions\CircularDepdencyException;
use Accolon\Container\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $binds = [];
    private string $currentClass = '';
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
        $this->currentClass = $id;
        return $this->make($id);
    }

    public function make(array|string|\Closure $id)
    {
        if (is_string($id) && isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        if (is_string($id) && !$this->has($id) && class_or_interface_exists($id)) {
            return $this->resolveObject($id);
        }

        if (is_array($id) || is_callable($id)) {
            return $this->resolveCallable($id);
        }

        if (!$this->has($id)) {
            return $this->resolveCallable($id);
        }

        $value = $this->binds[$id];

        if (is_string($value)) {
            return $this->resolveObject($value);
        }

        if (is_callable($value)) {
            return call_user_func($value, $this);
        }

        throw new NotFoundException('Id invalid!');
    }

    public function has(string $id)
    {
        return isset($this->binds[$id]);
    }

    public function resolveParameters(array $params): array
    {
        $newParams = [];

        /** @var \ReflectionParameter $param */
        foreach ($params as $param) {
            if ($param->isOptional()) {
                continue;
            }

            $name = (string) $param->getType();

            if ($name === $this->currentClass) {
                throw new CircularDepdencyException("Circular dependency of [{$this->currentClass}]");
            }

            if ($param->hasType() && class_or_interface_exists($name)) {
                $newParams[] = $this->make($name);
                continue;
            }
        }

        return $newParams;
    }

    public function resolveCallable(array|string|\Closure $action): mixed
    {
        if (is_array($action)) {
            $reflection = new \ReflectionFunction(\Closure::fromCallable([$action[0], $action[1]]));
        }

        $reflection = new \ReflectionFunction($action);

        $params = $this->resolveParameters($reflection->getParameters());

        return $reflection->invoke(...$params);
    }

    public function resolveObject(string $class): object
    {
        $reflector = new \ReflectionClass($class);

        if ($reflector->isInterface()) {
            throw new \ReflectionException("Interface can't instance");
        }

        $constructor = $reflector->getConstructor() ?? fn() => null;
        $params = ($constructor instanceof \ReflectionMethod) ? $constructor->getParameters() : [];

        if (empty($params)) {
            return $reflector->newInstance();
        }

        $newParams = $this->resolveParameters($params);

        return $reflector->newInstance(...$newParams);
    }
}
