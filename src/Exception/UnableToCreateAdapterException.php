<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\Exception;

class UnableToCreateAdapterException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function create(string $message, string $dsn): self
    {
        return new UnableToCreateAdapterException(sprintf(
            'Unable to create an adapter with DSN "%s": %s.',
            $dsn,
            $message
        ));
    }
}
