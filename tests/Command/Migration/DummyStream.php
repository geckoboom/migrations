<?php

declare(strict_types=1);

namespace Geckoboom\Migrations\Test\Command\Migration;

class DummyStream
{
    private string $stream;
    private array $stdout = [];
    private array $stdin = [];
    private array $stderr = [];
    public function stream_open($path, $mode, $options, &$opened_path): bool
    {
        $this->stream = \parse_url($path)['host'];
        return true;
    }

    public function stream_read()
    {
        return \array_shift($this->{$this->stream});
    }

    public function stream_eof(): bool
    {
        return true;
    }
    public function stream_write($data)
    {
        $this->{$this->stream}[] = $data;
    }
}
