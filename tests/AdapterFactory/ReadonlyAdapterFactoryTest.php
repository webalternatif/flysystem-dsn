<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\AdapterFactory;

use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use Tests\Webf\Flysystem\Dsn\AdapterFactory\Stub\AdapterFactoryStub;
use Tests\Webf\Flysystem\Dsn\AdapterFactory\Stub\AdapterStub;
use Webf\Flysystem\Dsn\AdapterFactory\ReadOnlyAdapterFactory;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

/**
 * @internal
 *
 * @covers \Webf\Flysystem\Dsn\AdapterFactory\ReadOnlyAdapterFactory
 * @covers \Webf\Flysystem\Dsn\Exception\DsnParameterException
 */
class ReadonlyAdapterFactoryTest extends TestCase
{
    public function test_create_adapter(): void
    {
        $factory = new ReadOnlyAdapterFactory(new AdapterFactoryStub());
        $adapter = $factory->createAdapter(
            'readonly(stub://inner1)'
        );

        $expectedAdapter = new ReadOnlyFilesystemAdapter(new AdapterStub('inner1'));

        $this->assertEquals($expectedAdapter, $adapter);
    }

    public function test_create_adapter_throws_exception_when_there_is_more_than_one_argument(): void
    {
        $factory = new ReadOnlyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnParameterException::class);
        $this->expectExceptionMessage('more than 1 argument in DSN');

        $factory->createAdapter('readonly(stub://inner1 stub://inner2)?name=name');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new ReadOnlyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new ReadOnlyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(UnsupportedDsnException::class);

        $factory->createAdapter('readonly://');
    }

    public function test_supports(): void
    {
        $factory = new ReadOnlyAdapterFactory(new AdapterFactoryStub());

        $this->assertTrue($factory->supports('readonly(inner://)'));
        $this->assertFalse($factory->supports('readonly://'));
        $this->assertFalse($factory->supports('onlyread(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new ReadOnlyAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnException::class);

        $factory->supports('Invalid DSN');
    }
}
