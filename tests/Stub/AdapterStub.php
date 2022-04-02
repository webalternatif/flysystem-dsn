<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\Stub;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;

class AdapterStub implements FilesystemAdapter
{
    public function __construct(public string $data)
    {
    }

    public function fileExists(string $path): bool
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function directoryExists(string $path): bool
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function write(string $path, string $contents, Config $config): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function read(string $path): string
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function readStream(string $path)
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function delete(string $path): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function deleteDirectory(string $path): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function createDirectory(string $path, Config $config): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function visibility(string $path): FileAttributes
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function mimeType(string $path): FileAttributes
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function lastModified(string $path): FileAttributes
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function fileSize(string $path): FileAttributes
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function listContents(string $path, bool $deep): iterable
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function move(string $source, string $destination, Config $config): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        throw new \LogicException('This is a stub adapter, you must not call this method.');
    }
}
