<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\FunctionsNotAllowedException;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

class InMemoryAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function createAdapter(string $dsn): InMemoryFilesystemAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        if ('in-memory' !== $dsn->getScheme()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        return new InMemoryFilesystemAdapter();
    }

    public function supports(string $dsn): bool
    {
        try {
            $scheme = DsnParser::parse($dsn)->getScheme() ?: '';
        } catch (FunctionsNotAllowedException) {
            return false;
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        return 'in-memory' === $scheme;
    }
}
