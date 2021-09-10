<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\Exception;

class MissingDsnParameterException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function create(string $missingParameter, string $dsn): self
    {
        return new MissingDsnParameterException(sprintf(
            'Parameter "%s" is missing from DSN "%s".',
            $missingParameter,
            $dsn
        ));
    }
}
