<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\AdapterFactory;

use League\Flysystem\FilesystemAdapter;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnableToCreateAdapterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

/**
 * Interface for factories that build Flysystem adapters from DSN.
 */
interface FlysystemAdapterFactoryInterface
{
    /**
     * Build the Flysystem adapter from the given DSN.
     *
     * @throws DsnException                   if the DSN is invalid (wrong syntax or format)
     * @throws DsnParameterException          if a DSN parameter or argument is missing or invalid
     * @throws UnableToCreateAdapterException if it's not possible to create the adapter
     * @throws UnsupportedDsnException        if the method is called whereas the DSN is not supported by this class
     */
    public function createAdapter(string $dsn): FilesystemAdapter;

    /**
     * Returns whether this class can build a Flysystem adapter with the given
     * DSN or not.
     *
     * @throws DsnException
     */
    public function supports(string $dsn): bool;
}
