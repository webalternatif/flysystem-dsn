<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\Exception;

class DsnParameterException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function invalidParameter(
        string $message,
        string $parameter,
        string $dsn,
    ): self {
        return new DsnParameterException(sprintf(
            'Parameter "%s" is invalid in DSN "%s": %s.',
            $parameter,
            $dsn,
            $message
        ));
    }

    public static function missingParameter(
        string $missingParameter,
        string $dsn,
    ): self {
        return new DsnParameterException(sprintf(
            'Parameter "%s" is missing from DSN "%s".',
            $missingParameter,
            $dsn
        ));
    }

    public static function missingArgument(
        int $minArgumentCount,
        int $actualArgumentCount,
        string $dsnName,
        string $dsn,
    ): self {
        return new DsnParameterException(sprintf(
            'There must be at least %s argument%s in DSN "%s", %d given in "%s".',
            $minArgumentCount,
            $minArgumentCount > 1 ? 's' : '',
            $dsnName,
            $actualArgumentCount,
            $dsn,
        ));
    }

    public static function toManyArguments(
        int $maxArgumentCount,
        int $actualArgumentCount,
        string $dsnName,
        string $dsn,
    ): self {
        return new DsnParameterException(sprintf(
            'There must not be more than %s argument%s in DSN "%s", %d given in "%s".',
            $maxArgumentCount,
            $maxArgumentCount > 1 ? 's' : '',
            $dsnName,
            $actualArgumentCount,
            $dsn,
        ));
    }
}
