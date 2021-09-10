<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\Exception;

use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\UnableToCreateAdapterException;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\Exception\UnableToCreateAdapterException
 */
class UnableToCreateAdapterExceptionTest extends TestCase
{
    public function test_constructor_is_private(): void
    {
        $class = new \ReflectionClass(UnableToCreateAdapterException::class);
        $this->assertTrue($class->getConstructor()->isPrivate());
    }

    public function test_message_and_dsn_are_present_in_message(): void
    {
        $message = 'too hard to build';
        $dsn = 'scheme://username:password@host/path?parameter=value';

        $exception = UnableToCreateAdapterException::create($message, $dsn);

        $this->assertStringContainsString($message, $exception->getMessage());
        $this->assertStringContainsString($dsn, $exception->getMessage());
    }
}
