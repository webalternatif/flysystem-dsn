<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\Stub;

use League\Flysystem\FilesystemAdapter;
use Nyholm\Dsn\DsnParser;
use Webf\Flysystem\Dsn\FlysystemAdapterFactoryInterface;

class AdapterFactoryStub implements FlysystemAdapterFactoryInterface
{
    public function createAdapter(string $dsn): FilesystemAdapter
    {
        return new AdapterStub(
            DsnParser::parse($dsn)
                ->withScheme(null)
                ->__toString()
        );
    }

    public function supports(string $dsn): bool
    {
        return 'stub' === DsnParser::parse($dsn)->getScheme();
    }
}
