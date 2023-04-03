<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Test\Command\Migration;

use DG\BypassFinals;
use Geckoboom\Migrations\Builder;
use Geckoboom\Migrations\Command\Migration\InstallCommand;
use Geckoboom\Migrations\Config\Config;
use Geckoboom\Migrations\Config\MigrationPath;
use Geckoboom\Migrations\Config\MigrationPaths;
use Geckoboom\Migrations\Domain\Migration;
use Geckoboom\Migrations\Domain\MigrationRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class InstallCommandTest extends TestCase
{
    private $stdin;
    private $stdout;
    private $stderr;
    private bool $needRestore = false;
    private MockObject $repository;
    private MockObject $config;
    private MockObject $container;
    private InstallCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();

        if ($this->needRestore = \in_array('test', \stream_get_wrappers())) {
            \stream_wrapper_unregister('test');
        }
        \stream_wrapper_register('test', DummyStream::class);

        $this->stdin = \fopen('test://stdin', 'r');
        $this->stdout = \fopen('test://stdout', 'w');
        $this->stderr = \fopen('test://stderr', 'w');
        $this->repository = $this->createMock(MigrationRepositoryInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->command = new InstallCommand(
            $this->repository,
            $this->config,
            $this->container,
            $this->stdin,
            $this->stdout,
            $this->stderr
        );
    }

    public function testRun()
    {
        $this->config->expects(self::atLeastOnce())
            ->method('getMigrationPaths')
            ->willReturn(
                new MigrationPaths([
                    new MigrationPath(
                        __DIR__ . '/data',
                        'Geckoboom\Migrations\Test\Command\Migration\data'
                    ),
                ])
            );

        $this->repository->expects(self::once())
            ->method('findMigrations')
            ->willReturn([new Migration('TestMigration210416005625', 210416005625)]);
        \fwrite($this->stdin, 'y');

        $this->container->expects(self::any())
            ->method('get')
            ->willReturnCallback(function (string $className) {
                return new $className($this->createMock(Builder::class));
            });

        $this->repository->expects(self::any())
            ->method('insert')
            ->with(self::isInstanceOf(Migration::class));;

        $actual = $this->command->run([1]);
        self::assertEquals(0, $actual);
    }
}
