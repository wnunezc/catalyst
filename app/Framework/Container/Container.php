<?php

declare(strict_types=1);

namespace Catalyst\Framework\Container;

use Catalyst\Framework\Traits\SingletonTrait;
use Closure;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

class Container
{
    use SingletonTrait;

    /**
     * @var array<string, array{concrete: Closure|string, shared: bool}>
     */
    private array $bindings = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @var array<string, bool>
     */
    private array $resolving = [];

    public function bind(string $abstract, Closure|string $concrete, bool $shared = false): self
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared'   => $shared,
        ];

        if (!$shared) {
            unset($this->instances[$abstract]);
        }

        return $this;
    }

    public function singleton(string $abstract, Closure|string|null $concrete = null): self
    {
        return $this->bind($abstract, $concrete ?? $abstract, true);
    }

    public function instance(string $abstract, mixed $instance): self
    {
        $this->instances[$abstract] = $instance;

        return $this;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->resolving[$abstract])) {
            throw new RuntimeException("Circular dependency detected while resolving '{$abstract}'.");
        }

        $this->resolving[$abstract] = true;

        try {
            $object = isset($this->bindings[$abstract])
                ? $this->resolveBinding($abstract, $this->bindings[$abstract])
                : $this->build($abstract);
        } finally {
            unset($this->resolving[$abstract]);
        }

        return $object;
    }

    /**
     * @param array{concrete: Closure|string, shared: bool} $binding
     */
    private function resolveBinding(string $abstract, array $binding): mixed
    {
        $concrete = $binding['concrete'];
        $object   = $concrete instanceof Closure
            ? $concrete($this)
            : $this->build($concrete);

        if ($binding['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build(string $concrete): mixed
    {
        if (!class_exists($concrete)) {
            throw new RuntimeException("Cannot resolve '{$concrete}': class does not exist.");
        }

        if ($this->supportsSingletonAccessor($concrete)) {
            return $concrete::getInstance();
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Cannot instantiate '{$concrete}'.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $concrete();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(sprintf(
                "Cannot resolve constructor parameter '\$%s' for '%s'.",
                $parameter->getName(),
                $concrete
            ));
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    private function supportsSingletonAccessor(string $concrete): bool
    {
        if (!method_exists($concrete, 'getInstance')) {
            return false;
        }

        $method = new \ReflectionMethod($concrete, 'getInstance');

        return $method->isStatic() && $method->isPublic() && $method->getNumberOfRequiredParameters() === 0;
    }
}
