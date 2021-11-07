<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\FtpAdapterFactory;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\FtpAdapterFactory
 */
class FtpAdapterFactoryTest extends TestCase
{
    /**
     * @dataProvider create_adapter_data_provider
     */
    public function test_create_adapter(string $dsn, FtpAdapter $expectedAdapter): void
    {
        $factory = new FtpAdapterFactory();
        $adapter = $factory->createAdapter($dsn);

        $this->assertEquals(
            $expectedAdapter,
            $adapter
        );
    }

    public function create_adapter_data_provider(): iterable
    {
        yield 'minimal' => [
            'ftp://username:password@host',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password')
            ),
        ];

        yield 'with "/" path' => [
            'ftp://username:password@host/',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password')
            ),
        ];

        yield 'with path' => [
            'ftp://username:password@host/path',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/path', 'username', 'password')
            ),
        ];

        yield 'with path containing spaces' => [
            'ftp://username:password@host/spaced%20path',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/spaced path', 'username', 'password')
            ),
        ];

        yield 'with custom port' => [
            'ftp://username:password@host:2121',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', port: 2121)
            ),
        ];

        yield 'with ssl' => [
            'ftp://username:password@host?ssl=true',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', ssl: true)
            ),
        ];

        yield 'with timeout' => [
            'ftp://username:password@host?timeout=123',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', timeout: 123)
            ),
        ];

        yield 'with utf8' => [
            'ftp://username:password@host?utf8=true',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', utf8: true)
            ),
        ];

        yield 'without passive' => [
            'ftp://username:password@host?passive=false',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', passive: false)
            ),
        ];

        yield 'with ascii transfer mode' => [
            'ftp://username:password@host?transfer_mode=ascii',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', transferMode: FTP_ASCII)
            ),
        ];

        yield 'with unix system type' => [
            'ftp://username:password@host?system_type=unix',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', systemType: 'unix')
            ),
        ];

        yield 'with windows system type' => [
            'ftp://username:password@host?system_type=windows',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', systemType: 'windows')
            ),
        ];

        yield 'with ignore passive address' => [
            'ftp://username:password@host?ignore_passive_address=true',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', ignorePassiveAddress: true)
            ),
        ];

        yield 'without ignore passive address' => [
            'ftp://username:password@host?ignore_passive_address=false',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', ignorePassiveAddress: false)
            ),
        ];

        yield 'with timestamps on unix listings' => [
            'ftp://username:password@host?timestamps_on_unix_listings=true',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', enableTimestampsOnUnixListings: true)
            ),
        ];

        yield 'with recurse manually' => [
            'ftp://username:password@host?recurse_manually=true',
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password', recurseManually: true)
            ),
        ];
    }

    /**
     * @dataProvider create_adapter_with_permission_parameter_data_provider
     */
    public function test_create_adapter_with_permission_parameter(string $parameters, array $permissionMap): void
    {
        $factory = new FtpAdapterFactory();
        $adapter = $factory->createAdapter("ftp://username:password@host?{$parameters}");

        $this->assertEquals(
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password'),
                visibilityConverter: PortableVisibilityConverter::fromArray($permissionMap)
            ),
            $adapter
        );
    }

    public function create_adapter_with_permission_parameter_data_provider(): iterable
    {
        yield [
            'public_file_permission=600',
            ['file' => ['public' => 0600]],
        ];

        yield [
            'private_file_permission=640',
            ['file' => ['private' => 0640]],
        ];

        yield [
            'public_dir_permission=700',
            ['dir' => ['public' => 0700]],
        ];

        yield [
            'private_dir_permission=755',
            ['dir' => ['private' => 0755]],
        ];
    }

    /**
     * @dataProvider visibilityDataProvider
     */
    public function test_create_adapter_with_default_dir_visibility(string $visibility): void
    {
        $factory = new FtpAdapterFactory();
        $adapter = $factory->createAdapter("ftp://username:password@host?default_dir_visibility={$visibility}");

        $this->assertEquals(
            new FtpAdapter(
                new FtpConnectionOptions('host', '/', 'username', 'password'),
                visibilityConverter: PortableVisibilityConverter::fromArray([], $visibility)
            ),
            $adapter
        );
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new FtpAdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    /**
     * @dataProvider unsupportedDsnDataProvider
     */
    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(string $scheme): void
    {
        $factory = new FtpAdapterFactory();

        try {
            $factory->createAdapter("{$scheme}://username:password@host");
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

    public function test_create_adapter_throws_exception_when_transfer_mode_is_invalid(): void
    {
        $factory = new FtpAdapterFactory();

        $this->expectException(InvalidDsnParameterException::class);

        $factory->createAdapter('ftp://username:password@host?transfer_mode=string');
    }

    public function test_create_adapter_throws_exception_when_system_type_is_invalid(): void
    {
        $factory = new FtpAdapterFactory();

        $this->expectException(InvalidDsnParameterException::class);

        $factory->createAdapter('ftp://username:password@host?system_type=macos');
    }

    /**
     * @dataProvider create_adapter_throws_exception_when_permission_parameter_is_invalid_data_provider
     */
    public function test_create_adapter_throws_exception_when_permission_parameter_is_invalid(string $parameter, string $permission): void
    {
        $factory = new FtpAdapterFactory();

        try {
            $factory->createAdapter("ftp://username:password@host?{$parameter}={$permission}");
            $this->fail(sprintf(
                'Failed asserting that exception of type "%s" is thrown.',
                InvalidDsnParameterException::class
            ));
        } catch (InvalidDsnParameterException) {
            $this->addToAssertionCount(1);
        } catch (\Throwable $t) {
            $this->fail(sprintf(
                'Failed asserting that exception of type "%s" matches expected exception "%s".',
                get_class($t),
                InvalidDsnParameterException::class
            ));
        }
    }

    public function create_adapter_throws_exception_when_permission_parameter_is_invalid_data_provider(): iterable
    {
        $parameters = [
            'public_file_permission',
            'private_file_permission',
            'public_dir_permission',
            'private_dir_permission',
        ];

        $unsupportedPermissions = ['00640', '64', 'public'];

        foreach ($parameters as $parameter) {
            foreach ($unsupportedPermissions as $permission) {
                yield [$parameter, $permission];
            }
        }
    }

    public function test_create_adapter_throws_exception_when_default_dir_visibility_is_invalid(): void
    {
        $factory = new FtpAdapterFactory();

        $this->expectException(InvalidDsnParameterException::class);

        $factory->createAdapter('ftp://username:password@host?default_dir_visibility=0755');
    }

    public function test_supports(): void
    {
        $factory = new FtpAdapterFactory();

        $this->assertTrue($factory->supports('ftp://username:password@host'));

        foreach ($this->unsupportedDsnDataProvider() as [$scheme]) {
            $this->assertFalse($factory->supports("{$scheme}://username:password@host"));
        }
        $this->assertFalse($factory->supports('ftp(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new FtpAdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->supports('Invalid DSN');
    }

    public function visibilityDataProvider(): iterable
    {
        yield 'public' => [Visibility::PUBLIC];
        yield 'private' => [Visibility::PRIVATE];
    }

    public function unsupportedDsnDataProvider(): iterable
    {
        yield ['remote'];
        yield ['sftp'];
        yield ['ftpp'];
    }
}
