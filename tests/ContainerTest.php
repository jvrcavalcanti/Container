<?php

use Accolon\Container\Container;
use Accolon\Container\Exceptions\CircularDepdencyException;
use Accolon\Container\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;

it('should return myself', function () {
    $container = new Container;

    expect($container->get(\stdClass::class))->toBeInstanceOf(\stdClass::class);
});

it('should return myself, but using helper 1', function () {
    expect(container()->get(\stdClass::class))->toBeInstanceOf(\stdClass::class);
});

it('should return myself, but using helper 2', function () {
    expect(resolve(\stdClass::class))->toBeInstanceOf(\stdClass::class);
});

it('should return concrete class', function () {
    $container = new Container;
    $container->bind(ContainerInterface::class, Container::class);

    expect($container->get(ContainerInterface::class))->toBeInstanceOf(Container::class);
});

it('should generate circular dependency exception', function () {
    $container = new Container;

    class A
    {
        public function __construct(B $foo)
        {
            //
        }
    }

    class B
    {
        public function __construct(A $foo)
        {
            //
        }
    }

    $container->get(A::class);
})->throws(CircularDepdencyException::class);

it('should return the return function', function () {
    $container = new Container;

    expect($container->make(function (\stdClass $obj) {
        $obj->message = 'oi';
        return $obj;
    }))
        ->message->toBe('oi');
});
