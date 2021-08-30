<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\FilesystemAdapter;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

interface FlysystemAdapterFactoryInterface
{
    /**
     * @throws InvalidDsnException
     * @throws MissingDsnParameterException
     * @throws UnsupportedDsnException
     */
    public function createAdapter(string $dsn): FilesystemAdapter;

    /**
     * @throws InvalidDsnException
     */
    public function supports(string $dsn): bool;
}
