<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Config;

class MigrationPath
{
    protected string $path;
    protected string $namespace;

    /**
     * @param string $path
     * @param string $namespace
     */
    public function __construct(string $path, string $namespace)
    {
        $this->path = $path;
        $this->namespace = $namespace;
        $this->ensurePathExist();
    }

    private function ensurePathExist(): void
    {
        if (!\is_dir($this->path)) {
            throw new \InvalidArgumentException(
                \sprintf('Migration path `%s` is not exist.', $this->path)
            );
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
