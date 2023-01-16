<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\SftpAdapterFactory;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\SftpAdapterFactory
 */
class SftpAdapterFactoryTest extends TestCase
{
    /**
     * @dataProvider create_adapter_data_provider
     */
    public function test_create_adapter(string $dsn, SftpAdapter $expectedAdapter): void
    {
        $factory = new SftpAdapterFactory();
        $adapter = $factory->createAdapter($dsn);

        $this->assertEquals(
            $expectedAdapter,
            $adapter
        );
    }

    public function create_adapter_data_provider(): iterable
    {
        yield 'minimal' => [
            'sftp://username@host',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username'),
                '/'
            ),
        ];

        yield 'with password' => [
            'sftp://username:password@host',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', 'password'),
                '/'
            ),
        ];

        yield 'with "/" path' => [
            'sftp://username@host/',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username'),
                '/'
            ),
        ];

        yield 'with path' => [
            'sftp://username@host/path',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username'),
                '/path'
            ),
        ];

        yield 'with path containing spaces' => [
            'sftp://username@host/spaced%20path',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username'),
                '/spaced path'
            ),
        ];

        yield 'with custom port' => [
            'sftp://username@host:2222',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', port: 2222),
                '/'
            ),
        ];

        yield 'with private key' => [
            'sftp://username@host?private_key=abc',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', privateKey: 'abc'),
                '/'
            ),
        ];

        yield 'with passphrase' => [
            'sftp://username@host?passphrase=abc',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', passphrase: 'abc'),
                '/'
            ),
        ];

        yield 'with use agent' => [
            'sftp://username@host?use_agent=true',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', useAgent: true),
                '/'
            ),
        ];

        yield 'with timeout' => [
            'sftp://username@host?timeout=123',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', timeout: 123),
                '/'
            ),
        ];

        yield 'with max retries' => [
            'sftp://username@host?max_retries=123',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', maxTries: 123),
                '/'
            ),
        ];

        yield 'with host fingerprint' => [
            'sftp://username@host?host_fingerprint=abc',
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username', hostFingerprint: 'abc'),
                '/'
            ),
        ];
    }

    /**
     * @dataProvider create_adapter_with_permission_parameter_data_provider
     */
    public function test_create_adapter_with_permission_parameter(string $parameters, array $permissionMap): void
    {
        $factory = new SftpAdapterFactory();
        $adapter = $factory->createAdapter("sftp://username@host?{$parameters}");

        $this->assertEquals(
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username'),
                '/',
                PortableVisibilityConverter::fromArray($permissionMap)
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
        $factory = new SftpAdapterFactory();
        $adapter = $factory->createAdapter("sftp://username@host?default_dir_visibility={$visibility}");

        $this->assertEquals(
            new SftpAdapter(
                new SftpConnectionProvider('host', 'username'),
                '/',
                PortableVisibilityConverter::fromArray([], $visibility)
            ),
            $adapter
        );
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new SftpAdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    /**
     * @dataProvider unsupportedDsnDataProvider
     */
    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(string $scheme): void
    {
        $factory = new SftpAdapterFactory();

        try {
            $factory->createAdapter("{$scheme}://username@host");
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

    /**
     * @dataProvider create_adapter_throws_exception_when_permission_parameter_is_invalid_data_provider
     */
    public function test_create_adapter_throws_exception_when_permission_parameter_is_invalid(string $parameter, string $permission): void
    {
        $factory = new SftpAdapterFactory();

        try {
            $factory->createAdapter("sftp://username@host?{$parameter}={$permission}");
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
        $factory = new SftpAdapterFactory();

        $this->expectException(InvalidDsnParameterException::class);

        $factory->createAdapter('sftp://username@host?default_dir_visibility=0755');
    }

    public function test_supports(): void
    {
        $factory = new SftpAdapterFactory();

        $this->assertTrue($factory->supports('sftp://username@host'));

        foreach ($this->unsupportedDsnDataProvider() as [$scheme]) {
            $this->assertFalse($factory->supports("{$scheme}://username@host"));
        }
        $this->assertFalse($factory->supports('sftp(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new SftpAdapterFactory();

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
        yield ['ftp'];
        yield ['ssftp'];
        yield ['sftpp'];
    }
}
