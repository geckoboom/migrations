<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Command\Migration;

use Geckoboom\Migrations\MigrationService;
use Whirlwind\App\Console\Command;

class StatusCommand extends Command
{
    protected MigrationService $service;

    public function __construct(
        MigrationService $service,
        $stdin = null,
        $stdout = null,
        $stderr = null
    ) {
        parent::__construct($stdin, $stdout, $stderr);
        $this->service = $service;
    }

    public function run(array $params = []): int
    {
        if (isset($params['all']) && $params['all']) {
            $limit = 0;
        } else {
            $limit = $params[0] ?? 10;
        }
        if ($limit < 1) {
            throw new \Exception('The limit must be greater than 0.');
        }
        $migrations = $this->service->getMigrationHistory($limit);

        if (empty($migrations)) {
            $this->info('No migration has been done before.');
        } else {
            $n = \count($migrations);
            if (!isset($params['all'])) {
                $this->info("Showing the last $n applied " . ($n === 1 ? 'migration' : 'migrations') . ":");
            } else {
                $this->stdout("Total $n " . ($n === 1 ? 'migration has' : 'migrations have') . ' been applied before:');
            }
            foreach ($migrations as $migration) {
                $this->output(
                    "\t(" . \date('Y-m-d H:i:s', $migration['createdAt']) . ') ' . $migration['name']
                );
            }
        }

        return 0;
    }


}
