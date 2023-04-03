<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Config;

class Config
{
    protected MigrationPaths $migrationPaths;
    protected string $collectionName;
    protected string $templateFilePath;

    /**
     * @param MigrationPaths $migrationPaths
     * @param string $collectionName
     * @param string $templateFilePath
     */
    public function __construct(
        MigrationPaths $migrationPaths,
        string $collectionName,
        string $templateFilePath
    ) {
        $this->migrationPaths = $migrationPaths;
        $this->collectionName = $collectionName;
        $this->templateFilePath = $templateFilePath;
        $this->ensureTemplateFileExist();
    }

    private function ensureTemplateFileExist(): void
    {
        if (!\is_file($this->templateFilePath)) {
            throw new \InvalidArgumentException(
                \sprintf('Template file `%s` is not exist.', $this->templateFilePath)
            );
        }
    }

    /**
     * @return MigrationPaths
     */
    public function getMigrationPaths(): MigrationPaths
    {
        return $this->migrationPaths;
    }

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * @return string
     */
    public function getTemplateFilePath(): string
    {
        return $this->templateFilePath;
    }
}
