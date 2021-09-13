<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\AwsS3AdapterFactory;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\FailoverAdapterFactory;
use Webf\Flysystem\Dsn\FlysystemAdapterFactory;
use Webf\Flysystem\Dsn\OpenStackSwiftAdapterFactory;
use Webf\Flysystem\OpenStackSwift\OpenStackSwiftAdapter;
use Webf\FlysystemFailoverBundle\Flysystem\FailoverAdapter;
use Webf\FlysystemFailoverBundle\MessageRepository\MessageRepositoryInterface;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\FlysystemAdapterFactory
 */
class FlysystemAdapterFactoryTest extends TestCase
{
    public function test_create_adapter(): void
    {
        $factory = new FlysystemAdapterFactory([
            new AwsS3AdapterFactory(),
            new FailoverAdapterFactory(
                new OpenStackSwiftAdapterFactory(),
                $this->createMock(MessageRepositoryInterface::class)
            ),
            new OpenStackSwiftAdapterFactory(),
        ]);

        $this->assertInstanceOf(
            OpenStackSwiftAdapter::class,
            $factory->createAdapter(
                'swift://username:password@host?region=region&container=container'
            )
        );

        $this->assertInstanceOf(
            FailoverAdapter::class,
            $factory->createAdapter(sprintf(
                'failover(%s %s)?name=default',
                'swift://username:password@host?region=region&container=container',
                'swift://username:password@host?region=region&container=container'
            ))
        );
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new FlysystemAdapterFactory([]);

        $this->expectException(InvalidDsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new FlysystemAdapterFactory([]);

        $this->expectException(UnsupportedDsnException::class);

        $factory->createAdapter('swift://username:password@host?region=region&container=container');
    }

    /**
     * @dataProvider supports_data_provider
     */
    public function test_supports(iterable $factories, string $dsn, bool $returnValue): void
    {
        $factory = new FlysystemAdapterFactory($factories);

        $this->assertEquals($returnValue, $factory->supports($dsn));
    }

    public function supports_data_provider(): iterable
    {
        yield 'without factory' => [
            [],
            'swift://username:password@host?region=region&container=container',
            false,
        ];

        yield 'with compatible factory' => [
            [new OpenStackSwiftAdapterFactory()],
            'swift://username:password@host?region=region&container=container',
            true,
        ];

        yield 'with incompatible factories' => [
            [new AwsS3AdapterFactory()],
            'swift://username:password@host?region=region&container=container',
            false,
        ];
    }
}
