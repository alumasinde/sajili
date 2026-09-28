<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    private array $instances = [];

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function make(string $class): object
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class {$class} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new RuntimeException("Cannot resolve \\${$parameter->getName()} for {$class}.");
            }

            $arguments[] = $this->make($type->getName());
        }

        return $reflection->newInstanceArgs($arguments);
    }
}
