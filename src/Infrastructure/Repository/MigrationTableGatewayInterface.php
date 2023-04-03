<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Infrastructure\Repository;

use Whirlwind\Infrastructure\Repository\TableGateway\TableGatewayInterface;

interface MigrationTableGatewayInterface extends TableGatewayInterface
{
    public function queryOrCreateCollection(array $conditions = [], int $limit = 0, array $order = []): array;
}
