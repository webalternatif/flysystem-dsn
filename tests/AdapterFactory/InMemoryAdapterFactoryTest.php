<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\AdapterFactory;

use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Visibility;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\AdapterFactory\InMemoryAdapterFactory;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

/**
 * @internal
 *
 * @covers \Webf\Flysystem\Dsn\AdapterFactory\InMemoryAdapterFactory
 * @covers \Webf\Flysystem\Dsn\Exception\DsnParameterException
 */
class InMemoryAdapterFactoryTest extends TestCase
{
    public function test_create_adapter(): void
    {
        $factory = new InMemoryAdapterFactory();

        $this->assertEquals(
            new InMemoryFilesystemAdapter(),
            $factory->createAdapter('in-memory://')
        );

        $this->assertEquals(
            new InMemoryFilesystemAdapter(Visibility::PRIVATE),
            $factory->createAdapter('in-memory://?default_visibility=private')
        );
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new InMemoryAdapterFactory();

        $this->expectException(DsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new InMemoryAdapterFactory();

        $unsupportedSchemes = ['in', 'memory', '-in-memory', 'in-memory-'];

        foreach ($unsupportedSchemes as $scheme) {
            try {
                $factory->createAdapter("{$scheme}://");
                $this->fail(sprintf(
                    'Failed asserting that exception of type "%s" is thrown.',
                    UnsupportedDsnException::class
                ));
            } catch (UnsupportedDsnException) {
                $this->addToAssertionCount(1);
            } catch (\Throwable $t) {
                $this->fail(sprintf(
                    'Failed asserting that exception of type "%s" matches expected exception "%s".',
                    get_class($t),
                    UnsupportedDsnException::class
                ));
            }
        }
    }

    public function test_create_adapter_throws_exception_when_default_visibility_is_invalid(): void
    {
        $factory = new InMemoryAdapterFactory();

        $this->expectException(DsnParameterException::class);

        $factory->createAdapter('in-memory://?default_visibility=0755');
    }

    public function test_supports(): void
    {
        $factory = new InMemoryAdapterFactory();

        $this->assertTrue($factory->supports('in-memory://'));

        $unsupportedSchemes = ['in', 'memory', '-in-memory', 'in-memory-'];
        foreach ($unsupportedSchemes as $scheme) {
            $this->assertFalse($factory->supports("{$scheme}://"));
        }
        $this->assertFalse($factory->supports('in-memory(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new InMemoryAdapterFactory();

        $this->expectException(DsnException::class);

        $factory->supports('Invalid DSN');
    }
}
