<?php

declare(strict_types=1);

namespace Geckoboom\Migrations;

/**
 * @method create(string $collection, callable $callback)
 * @method createIfNotExists(string $collection, callable $callback)
 * @method modify(string $collection, callable $callback)
 * @method drop(string $collection)
 * @method dropIfExists(string $collection)
 */
abstract class Migration
{
    protected Builder $builder;

    /**
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    abstract public function up(): void;

    abstract public function down(): void;

    public function __call($name, $arguments)
    {
        if (!\method_exists($this->builder, $name)) {
            throw new \BadMethodCallException(
                \sprintf('Builder has no method with name `%s`', $name)
            );
        }

        return $this->builder->$name(...$arguments);
    }
}
