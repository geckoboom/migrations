<?php

declare(strict_types=1);

namespace Geckoboom\Migrations;

use Geckoboom\Migrations\Config\Config;
use Geckoboom\Migrations\Domain\MigrationRepositoryInterface;

class MigrationService
{
    protected Config $config;
    protected MigrationRepositoryInterface $repository;

    /**
     * @param Config $config
     * @param MigrationRepositoryInterface $repository
     */
    public function __construct(
        Config $config,
        MigrationRepositoryInterface $repository
    ) {
        $this->config = $config;
        $this->repository = $repository;
    }

    /**
     * @param int $limit
     * @return array
     * @throws MigrationException
     */
    public function getMigrationHistory(int $limit = 0): array
    {
        $histories = $this->repository->findMigrations(
            [],
            $limit,
            ['createdAt' => SORT_DESC, 'name' => SORT_DESC]
        );

        $historyMap = [];
        foreach ($histories as $history) {
            $historyMap[$history->getName()] = $this->resolveName($history->getName()) + [
                'createdAt' => $history->getCreatedAt(),
            ];
        }

        return $historyMap;
    }

    protected function resolveName(string $name): array
    {
        foreach ($this->config->getMigrationPaths() as $migrationPath) {
            $filePath = \sprintf(
                '%s%s%s.php',
                \rtrim($migrationPath->getPath(), DIRECTORY_SEPARATOR),
                DIRECTORY_SEPARATOR,
                $name
            );

            if (\file_exists($filePath)) {
                return [
                    'name' => $name,
                    'fullName' =>\sprintf(
                        '%s\\%s',
                        \rtrim($migrationPath->getNamespace(), '\\'),
                        $name
                    ),
                    'path' => $filePath,
                ];
            }
        }

        throw new MigrationException(\sprintf('No migration path found for migration `%s`'), $name);
    }

    public function getPendingMigrations(int $limit = 0): array
    {
        $applied = $this->getMigrationHistory();

        $migrationMap = [];
        foreach ($this->config->getMigrationPaths() as $migrationPath) {
            $handle = \opendir($migrationPath->getPath());
            while (($file = \readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $path = \sprintf(
                    '%s%s%s',
                    \rtrim($migrationPath->getPath(), DIRECTORY_SEPARATOR),
                    DIRECTORY_SEPARATOR,
                    $file
                );

                if (\is_file($path) && \preg_match('/^(.*?(\d{12})).php$/', $file, $matches)
                    && !\array_key_exists($matches[1], $applied)
                ) {
                    $migrationMap[$matches[1]] = [
                        'name' => $matches[1],
                        'fullName' => \sprintf('%s\\%s', \trim($migrationPath->getNamespace()), $matches[1]),
                        'path' => $path,
                        'createdAt' => $matches[2],
                    ];
                }
            }
            \closedir($handle);
        }

        \usort($migrationMap, function (array $a, array $b): int {
            if ($a['createdAt'] == $b['createdAt']) {
                return 0;
            }

            return $a['createdAt'] < $b['createdAt'] ? -1 : 1;
        });

        if ($limit > 0) {
            $migrationMap = \array_slice(\array_values($migrationMap), 0, $limit);
        }

        return $migrationMap;
    }

    /**
     * @param string $name
     * @param int $createdAt
     * @return Domain\Migration
     */
    public function addMigration(string $name, int $createdAt): \Geckoboom\Migrations\Domain\Migration
    {
        $entity = new \Geckoboom\Migrations\Domain\Migration(
            $name,
            $createdAt
        );

        $this->repository->insert($entity);

        return $entity;
    }

    public function getMigrationsForRollback(int $limit = 0): array
    {
        return $this->getMigrationHistory($limit);
    }

    public function deleteMigration(string $name): void
    {
        $this->repository->deleteByName($name);
    }
}
