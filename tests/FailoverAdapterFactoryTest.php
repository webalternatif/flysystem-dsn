<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use PHPUnit\Framework\TestCase;
use Tests\Webf\Flysystem\Dsn\Stub\AdapterFactoryStub;
use Tests\Webf\Flysystem\Dsn\Stub\AdapterStub;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\FailoverAdapterFactory;
use Webf\FlysystemFailoverBundle\Flysystem\FailoverAdapter;
use Webf\FlysystemFailoverBundle\Flysystem\InnerAdapter;
use Webf\FlysystemFailoverBundle\MessageRepository\MessageRepositoryInterface;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\FailoverAdapterFactory
 */
class FailoverAdapterFactoryTest extends TestCase
{
    public function test_create_adapter(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $messageRepository = $this->createMock(
                MessageRepositoryInterface::class
            )
        );
        $adapter = $factory->createAdapter(
            'failover(stub://inner1 stub://inner2)?name=name'
        );

        $expectedAdapter = new FailoverAdapter(
            'name',
            [
                new InnerAdapter(new AdapterStub('inner1')),
                new InnerAdapter(new AdapterStub('inner2')),
            ],
            $messageRepository
        );

        $this->assertEquals($expectedAdapter, $adapter);
    }

    public function test_create_adapter_handles_time_shift_option(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $messageRepository = $this->createMock(
                MessageRepositoryInterface::class
            )
        );
        $adapter = $factory->createAdapter(
            'failover(stub://inner1?time_shift=123 stub://inner2?time_shift=zero)?name=name'
        );

        $expectedAdapter = new FailoverAdapter(
            'name',
            [
                new InnerAdapter(new AdapterStub('inner1'), ['time_shift' => 123]),
                new InnerAdapter(new AdapterStub('inner2'), ['time_shift' => 0]),
            ],
            $messageRepository
        );

        $this->assertEquals($expectedAdapter, $adapter);
    }

    public function test_create_adapter_throws_exception_when_name_parameter_is_missing(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $this->createMock(MessageRepositoryInterface::class)
        );

        $this->expectException(MissingDsnParameterException::class);

        $factory->createAdapter('failover(stub://inner1 stub://inner2)');
    }

    public function test_create_adapter_throws_exception_when_there_is_less_than_two_arguments(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $this->createMock(MessageRepositoryInterface::class)
        );

        $this->expectException(MissingDsnParameterException::class);

        $factory->createAdapter('failover(stub://inner1)?name=name');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $this->createMock(MessageRepositoryInterface::class)
        );

        $this->expectException(InvalidDsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $this->createMock(MessageRepositoryInterface::class)
        );

        $this->expectException(UnsupportedDsnException::class);

        $factory->createAdapter('failover://');
    }

    public function test_supports(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $this->createMock(MessageRepositoryInterface::class)
        );

        $this->assertTrue($factory->supports('failover(inner://)'));
        $this->assertFalse($factory->supports('failover://'));
        $this->assertFalse($factory->supports('overfail(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new FailoverAdapterFactory(
            new AdapterFactoryStub(),
            $this->createMock(MessageRepositoryInterface::class)
        );

        $this->expectException(InvalidDsnException::class);

        $factory->supports('Invalid DSN');
    }
}
