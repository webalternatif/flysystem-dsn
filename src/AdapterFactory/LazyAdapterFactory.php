<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\AdapterFactory;

use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Adapter\LazyAdapter;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

readonly class LazyAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function __construct(private FlysystemAdapterFactoryInterface $adapterFactory)
    {
    }

    public function createAdapter(string $dsn): LazyAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parseFunc($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        if ('lazy' !== $dsn->getName()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        if (count($arguments = $dsn->getArguments()) > 1) {
            throw DsnParameterException::toManyArguments(1, count($arguments), 'lazy', $dsnString);
        }

        return new LazyAdapter($this->adapterFactory, $arguments[0]->__toString());
    }

    public function supports(string $dsn): bool
    {
        try {
            $name = DsnParser::parseFunc($dsn)->getName() ?: '';
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        return 'lazy' === $name;
    }
}
