<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\AdapterFactory;

use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

readonly class ReadOnlyAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function __construct(private FlysystemAdapterFactoryInterface $adapterFactory)
    {
    }

    public function createAdapter(string $dsn): ReadOnlyFilesystemAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parseFunc($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        if ('readonly' !== $dsn->getName()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        if (count($arguments = $dsn->getArguments()) > 1) {
            throw DsnParameterException::toManyArguments(1, count($arguments), 'readonly', $dsnString);
        }

        $innerAdapter = $this->adapterFactory->createAdapter($arguments[0]->__toString());

        return new ReadOnlyFilesystemAdapter($innerAdapter);
    }

    public function supports(string $dsn): bool
    {
        try {
            $name = DsnParser::parseFunc($dsn)->getName() ?: '';
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        return 'readonly' === $name;
    }
}
