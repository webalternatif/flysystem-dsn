<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\Exception;

use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\FlysystemAdapterFactory;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\Exception\UnsupportedDsnException
 */
class UnsupportedDsnExceptionTest extends TestCase
{
    public function test_constructor_is_private(): void
    {
        $class = new \ReflectionClass(UnsupportedDsnException::class);
        $this->assertTrue($class->getConstructor()->isPrivate());
    }

    public function test_factory_class_name_and_dsn_are_present_in_message(): void
    {
        $factory = new FlysystemAdapterFactory([]);
        $dsn = 'scheme://username:password@host/path?parameter=value';

        $exception = UnsupportedDsnException::create($factory, $dsn);

        $this->assertStringContainsString($factory::class, $exception->getMessage());
        $this->assertStringContainsString($dsn, $exception->getMessage());
    }
}
