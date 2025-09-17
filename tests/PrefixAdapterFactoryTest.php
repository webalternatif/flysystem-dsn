<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use League\Flysystem\PathPrefixing\PathPrefixedAdapter;
use PHPUnit\Framework\TestCase;
use Tests\Webf\Flysystem\Dsn\Stub\AdapterFactoryStub;
use Tests\Webf\Flysystem\Dsn\Stub\AdapterStub;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\PrefixAdapterFactory;

/**
 * @internal
 *
 * @covers \Webf\Flysystem\Dsn\Exception\DsnParameterException
 * @covers \Webf\Flysystem\Dsn\PrefixAdapterFactory
 */
class PrefixAdapterFactoryTest extends TestCase
{
    public function test_create_adapter(): void
    {
        $factory = new PrefixAdapterFactory(new AdapterFactoryStub());
        $adapter = $factory->createAdapter('prefix(stub://inner1)?path=some/prefix/path');

        $expectedAdapter = new PathPrefixedAdapter(new AdapterStub('inner1'), 'some/prefix/path');

        $this->assertEquals($expectedAdapter, $adapter);
    }

    public function test_create_adapter_throws_exception_when_path_parameter_is_missing(): void
    {
        $factory = new PrefixAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnParameterException::class);

        $factory->createAdapter('prefix(stub://inner1)');
    }

    public function test_create_adapter_throws_exception_when_there_is_more_than_one_argument(): void
    {
        $factory = new PrefixAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnParameterException::class);
        $this->expectExceptionMessage('more than 1 argument in DSN');

        $factory->createAdapter('prefix(stub://inner1 stub://inner2)?path=some/prefix/path');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new PrefixAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new PrefixAdapterFactory(new AdapterFactoryStub());

        $this->expectException(UnsupportedDsnException::class);

        $factory->createAdapter('prefix://');
    }

    public function test_supports(): void
    {
        $factory = new PrefixAdapterFactory(new AdapterFactoryStub());

        $this->assertTrue($factory->supports('prefix(inner://)'));
        $this->assertFalse($factory->supports('prefix://'));
        $this->assertFalse($factory->supports('fixpre(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new PrefixAdapterFactory(new AdapterFactoryStub());

        $this->expectException(DsnException::class);

        $factory->supports('Invalid DSN');
    }
}
