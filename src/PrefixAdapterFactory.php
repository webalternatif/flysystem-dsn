<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

readonly class PrefixAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function __construct(private FlysystemAdapterFactoryInterface $adapterFactory)
    {
    }

    public function createAdapter(string $dsn): PathPrefixedAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parseFunc($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        if ('prefix' !== $dsn->getName()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        if (count($arguments = $dsn->getArguments()) > 1) {
            throw DsnParameterException::toManyArguments(1, count($arguments), 'prefix', $dsnString);
        }

        if (!is_string($path = $dsn->getParameter('path'))) {
            throw DsnParameterException::missingParameter('path', $dsnString);
        }

        $innerAdapter = $this->adapterFactory->createAdapter($arguments[0]->__toString());

        return new PathPrefixedAdapter($innerAdapter, $path);
    }

    public function supports(string $dsn): bool
    {
        try {
            $name = DsnParser::parseFunc($dsn)->getName() ?: '';
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        return 'prefix' === $name;
    }
}
