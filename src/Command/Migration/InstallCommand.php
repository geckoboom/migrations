<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Command\Migration;

use Geckoboom\Migrations\Migration;
use Geckoboom\Migrations\MigrationException;
use Geckoboom\Migrations\MigrationService;
use Psr\Container\ContainerInterface;
use Whirlwind\App\Console\Command;

class InstallCommand extends Command
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
        $limit = (int) $params[0] ?? 0;
        $migrations = $this->service->getPendingMigrations($limit);

        if (empty($migrations)) {
            $this->info('No new migrations found. Your system is up-to-date.');

            return 0;
        }

        $n = \count($migrations);
        if ($limit === 0) {
            $this->info("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:");
        } else {
            $this->info(
                "$n new " . ($n === 1 ? 'migration' : 'migrations')
                . " to be applied:"
            );
        }

        foreach ($migrations as $migration) {
            $this->output('\t' . $migration['name']);
        }
        $this->output('');

        if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            $applied = 0;
            foreach ($migrations as $migration) {
                if (!$this->applyMigration($migration)) {
                    $this->error(
                        "$applied from $n " . ($applied === 1 ? 'migration was' : 'migrations were')
                        . ' applied.'
                    );
                    $this->error('Migration failed. The rest of the migrations are canceled.');

                    return 1;
                }
                $applied++;
            }
            $this->success("$n " . ($n === 1 ? 'migration was' : 'migrations were') . " applied.");
            $this->success('Migrated up successfully.');

        }

        return 0;
    }

    protected function applyMigration(array $data): bool
    {
        $this->info("*** applying {$data['name']}");
        $start = \microtime(true);
        /** @var Migration $migration */
        $migration = $this->container->get($data['fullName']);

        try {
            $migration->up();
            $this->service->addMigration($data['name'], (int) $data['createdAt']);
            $time = \microtime(true) - $start;
            $this->success("*** applied {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");

            return true;
        } catch (MigrationException $e) {
            $time = \microtime(true) - $start;
            $this->error("*** failed to apply {$data['name']} (time: " . \sprintf('%.3f', $time) . "s)");
        }

        return false;
    }
}
