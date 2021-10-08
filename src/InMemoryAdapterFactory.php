<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Visibility;
use Nyholm\Dsn\Configuration\Dsn;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\FunctionsNotAllowedException;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnParameterException;
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

        $defaultVisibility = $this->getStringParameter($dsn, 'default_visibility') ?: Visibility::PUBLIC;
        if (!in_array($defaultVisibility, [Visibility::PUBLIC, Visibility::PRIVATE])) {
            throw InvalidDsnParameterException::create(sprintf('must be "%s" or "%s"', Visibility::PUBLIC, Visibility::PRIVATE), 'default_visibility', $dsnString);
        }

        return new InMemoryFilesystemAdapter($defaultVisibility);
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

    private function getStringParameter(Dsn $dsn, string $parameter): ?string
    {
        if (!is_string($value = $dsn->getParameter($parameter))) {
            return null;
        }

        return $value;
    }
}
