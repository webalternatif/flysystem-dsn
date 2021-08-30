<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\Exception;

use Nyholm\Dsn\Configuration\Dsn;
use Webf\Flysystem\Dsn\FlysystemAdapterFactoryInterface;

class UnsupportedDsnException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function create(
        FlysystemAdapterFactoryInterface $factory,
        Dsn $dsn
    ): self {
        return new UnsupportedDsnException(sprintf(
            'Factory "%s" does not support DSN "%s".',
            $factory::class,
            $dsn->__toString()
        ));
    }
}
