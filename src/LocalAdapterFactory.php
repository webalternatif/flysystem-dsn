<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\FunctionsNotAllowedException;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

class LocalAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function createAdapter(string $dsn): LocalFilesystemAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        if ('local' !== $dsn->getScheme()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        return new LocalFilesystemAdapter(
            $this->decodePath(($dsn->getHost() ?: '') . ($dsn->getPath() ?: ''))
        );
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

        return 'local' === $scheme;
    }

    private function decodePath(string $path): string
    {
        return str_replace('%20', ' ', $path);
    }
}
