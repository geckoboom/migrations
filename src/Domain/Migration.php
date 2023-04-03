<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Domain;

class Migration
{
    private string $name;
    private int $createdAt;

    /**
     * @param string $name
     * @param int $createdAt
     */
    public function __construct(string $name, int $createdAt)
    {
        $this->name = $name;
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
}
