<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\FilesystemAdapter;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

class FlysystemAdapterFactory implements FlysystemAdapterFactoryInterface
{
    /**
     * @param iterable<FlysystemAdapterFactoryInterface> $factories
     */
    public function __construct(private iterable $factories)
    {
    }

    public function createAdapter(string $dsn): FilesystemAdapter
    {
        try {
            DsnParser::parseFunc($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn)) {
                return $factory->createAdapter($dsn);
            }
        }

        throw UnsupportedDsnException::create($this, $dsn);
    }

    public function supports(string $dsn): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($dsn)) {
                return true;
            }
        }

        return false;
    }
}
