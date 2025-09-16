<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\Exception;

use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;

/**
 * @internal
 *
 * @covers \Webf\Flysystem\Dsn\Exception\DsnParameterException
 */
class DsnParameterExceptionTest extends TestCase
{
    public function test_constructor_is_private(): void
    {
        $class = new \ReflectionClass(DsnParameterException::class);
        $this->assertTrue($class->getConstructor()->isPrivate());
    }

    public function test_adapter_message_is_pluralized(): void
    {
        $dsnName = 'function';
        $dsn = "{$dsnName}(null://)";

        $exception = DsnParameterException::missingArgument(1, 0, $dsnName, $dsn);

        $this->assertStringContainsString('1 argument', $exception->getMessage());
        $this->assertStringNotContainsString('1 arguments', $exception->getMessage());
    }
}
