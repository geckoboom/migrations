<?php

declare(strict_types=1);

namespace Geckoboom\Migrations;

interface BlueprintFactoryInterface
{
    public function create(string $collection): BlueprintInterface;
}
