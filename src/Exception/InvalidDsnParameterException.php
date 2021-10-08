<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\Exception;

class InvalidDsnParameterException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function create(
        string $message,
        string $parameter,
        string $dsn
    ): self {
        return new InvalidDsnParameterException(sprintf(
            'Parameter "%s" is invalid in DSN "%s": %s.',
            $parameter,
            $dsn,
            $message
        ));
    }
}
