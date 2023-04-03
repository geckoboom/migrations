<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Command\Migration;

use Geckoboom\Migrations\Migration;
use Geckoboom\Migrations\MigrationException;
use Geckoboom\Migrations\MigrationService;
use Psr\Container\ContainerInterface;
use Whirlwind\App\Console\Command;

class RollbackCommand extends Command
{
    protected MigrationService $service;
    protected ContainerInterface $container;

    public function __construct(
        MigrationService $service,
        ContainerInterface $container,
        $stdin = null,
        $stdout = null,
        $stderr = null
    ) {
        parent::__construct($stdin, $stdout, $stderr);
        $this->service = $service;
        $this->container = $container;
    }

    public function run(array $params = []): int
    {
        if (isset($params['all']) && $params['all']) {
            $limit = 0;
        } else {
            $limit = (int) ($params[0] ?? 1);
        }

        if ($limit < 1) {
            throw new \Exception('The limit must be greater than 0.');
        }

        $migrations = $this->service->getMigrationsForRollback($limit);
        if (empty($migrations)) {
            $this->info('No migration has been done before.');

            return 0;
        }

        $n = \count($migrations);
        $this->info("Total $n " . ($n === 1 ? 'migration' : 'migrations') . ' to be reverted:');

        foreach ($migrations as $migration) {
            $this->output("$migration");
        }

        $reverted = 0;
        if ($this->confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateRollback($migration)) {
                    $this->error(
                        "$reverted from $n " . ($reverted === 1 ? 'migration was' : 'migrations were')
                        . ' reverted.'
                    );
                    $this->error('Migration failed. The rest of the migrations are canceled.');

                    return 1;
                }
                $reverted++;
            }
            $this->success("$n " . ($n === 1 ? 'migration was' : 'migrations were') . ' reverted.');
            $this->success('Migrated down successfully.');
        }

        return 0;
    }

    protected function migrateRollback(array $data): bool
    {
        $this->info("*** reverting {$data['name']}");
        $start = \microtime(true);
        /** @var Migration $migration */
        $migration = $this->container->get($data['fullName']);
        try {
            $migration->down();
            $this->service->deleteMigration($data['name']);
            $time = \microtime(true) - $start;
            $this->success("*** reverted {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");

            return true;
        } catch (MigrationException $e) {
            $time = \microtime(true) - $start;
            $this->error("*** failed to revert {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");
        }

        return false;
    }
}
