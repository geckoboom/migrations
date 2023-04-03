<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Test\Command\Migration;

use DG\BypassFinals;
use Geckoboom\Migrations\Command\Migration\CreateCommand;
use Geckoboom\Migrations\Config\Config;
use Geckoboom\Migrations\Config\MigrationPath;
use Geckoboom\Migrations\Config\MigrationPaths;
use PHPUnit\Framework\TestCase;

class CreateCommandTest extends TestCase
{
    private $stdin;
    private $stdout;
    private $stderr;
    private bool $needRestore = false;
    private string $migrationFile = 'create_tests_table';
    private CreateCommand $command;

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

        $this->command = new CreateCommand(
            new Config(
                new MigrationPaths([
                    new MigrationPath(__DIR__, 'Geckoboom\Migrations\Test\Command\Migration\data')
                ]),
                'migrations',
                __DIR__ . '/../../../src/template/migration.php',
            ),
            $this->stdin,
            $this->stdout,
            $this->stderr
        );
    }

    public function testRun()
    {
        \fwrite($this->stdin, 'y');
        $actual = $this->command->run([$this->migrationFile]);
        self::assertEquals(0, $actual);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \fclose($this->stdin);
        \fclose($this->stdout);
        \fclose($this->stderr);

        $name = \camelize($this->migrationFile);
        foreach (\scandir(__DIR__) as $fileName) {
            if (\substr($fileName, 0, \strlen($name)) === $name) {
                \unlink(__DIR__ . DIRECTORY_SEPARATOR . $fileName);
            }
        }

        if ($this->needRestore) {
            \stream_wrapper_restore('test');
        }
    }
}
