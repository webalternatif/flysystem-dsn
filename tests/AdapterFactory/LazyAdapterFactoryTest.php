<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\AdapterFactory;

use PHPUnit\Framework\TestCase;
use Tests\Webf\Flysystem\Dsn\AdapterFactory\Stub\AdapterFactoryStub;
use Webf\Flysystem\Dsn\Adapter\LazyAdapter;
use Webf\Flysystem\Dsn\AdapterFactory\LazyAdapterFactory;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

/**
 * @internal
 *
 * @covers \Webf\Flysystem\Dsn\AdapterFactory\LazyAdapterFactory
 * @covers \Webf\Flysystem\Dsn\Exception\DsnParameterException
 */
class LazyAdapterFactoryTest extends TestCase
{
    public function test_create_adapter(): void
    {
        $factory = new LazyAdapterFactory(new AdapterFactoryStub());
        $adapter = $factory->createAdapter('lazy(stub://inner)');

        $expectedAdapter = new LazyAdapter(new AdapterFactoryStub(), 'stub://inner');

        $this->assertEquals($expectedAdapter, $adapter);
    }

    public function test_create_adapter_throws_exception_when_there_is_more_than_one_argument(): void
    {
        $factory = new LazyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnParameterException::class);
        $this->expectExceptionMessage('more than 1 argument in DSN');

        $factory->createAdapter('lazy(stub://inner1 stub://inner2)');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new LazyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new LazyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(UnsupportedDsnException::class);

        $factory->createAdapter('lazy://');
    }

    public function test_supports(): void
    {
        $factory = new LazyAdapterFactory(new AdapterFactoryStub());

        $this->assertTrue($factory->supports('lazy(inner://)'));
        $this->assertFalse($factory->supports('lazy://'));
        $this->assertFalse($factory->supports('eager(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new LazyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnException::class);

        $factory->supports('Invalid DSN');
    }
}
