<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\LocalAdapterFactory;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\LocalAdapterFactory
 */
class LocalAdapterFactoryTest extends TestCase
{
    /**
     * @dataProvider create_adapter_data_provider
     */
    public function test_create_adapter(string $path): void
    {
        $factory = new LocalAdapterFactory();
        $adapter = $factory->createAdapter("local://{$path}");

        $this->assertEquals(
            new LocalFilesystemAdapter(str_replace('%20', ' ', $path)),
            $adapter
        );
    }

    public function create_adapter_data_provider(): iterable
    {
        yield 'relative path' => ['tests/Stub'];
        yield 'relative path 2' => ['./tests/Stub'];
        yield 'absolute path' => [str_replace(' ', '%20', __DIR__) . '/Stub'];
        yield 'path with space' => ['var/test%201'];
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new LocalAdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new LocalAdapterFactory();

        $unsupportedSchemes = ['file', 'locale', 'alocal'];

        foreach ($unsupportedSchemes as $scheme) {
            try {
                $factory->createAdapter("{$scheme}://var");
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

    public function test_supports(): void
    {
        $factory = new LocalAdapterFactory();

        $this->assertTrue($factory->supports('local://var'));

        $unsupportedSchemes = ['file', 'locale', 'alocal'];
        foreach ($unsupportedSchemes as $scheme) {
            $this->assertFalse($factory->supports("{$scheme}://var"));
        }
        $this->assertFalse($factory->supports('local(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new LocalAdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->supports('Invalid DSN');
    }
}
