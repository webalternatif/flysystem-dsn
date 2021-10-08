<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\Exception;

use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\InvalidDsnParameterException;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\Exception\InvalidDsnParameterException
 */
class InvalidDsnParameterExceptionTest extends TestCase
{
    public function test_constructor_is_private(): void
    {
        $class = new \ReflectionClass(InvalidDsnParameterException::class);
        $this->assertTrue($class->getConstructor()->isPrivate());
    }

    public function test_message_missing_parameter_and_dsn_are_present_in_message(): void
    {
        $message = 'foo';
        $parameter = 'bar';
        $dsn = 'scheme://username:password@host/path?parameter=value';

        $exception = InvalidDsnParameterException::create($message, $parameter, $dsn);

        $this->assertStringContainsString($message, $exception->getMessage());
        $this->assertStringContainsString($parameter, $exception->getMessage());
        $this->assertStringContainsString($dsn, $exception->getMessage());
    }
}
