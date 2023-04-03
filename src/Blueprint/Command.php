<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Blueprint;

use Whirlwind\Infrastructure\Persistence\ConnectionInterface;

abstract class Command
{
    protected string $collection;

    /**
     * @param string $collection
     * @param array $args
     */
    public function __construct(
        string $collection,
        array $args = []
    ) {
        $this->collection = $collection;

        foreach ($args as $property => $value) {
            $this->$property = $value;
        }
    }

    abstract public function apply(ConnectionInterface $connection);
}
