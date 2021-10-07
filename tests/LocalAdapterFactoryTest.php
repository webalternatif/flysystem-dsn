<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\UnableToCreateAdapterException;
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

    /**
     * @dataProvider create_adapter_with_permission_parameter_data_provider
     */
    public function test_create_adapter_with_permission_parameter(string $parameters, array $permissionMap): void
    {
        $factory = new LocalAdapterFactory();
        $adapter = $factory->createAdapter("local://var?{$parameters}");

        $this->assertEquals(
            new LocalFilesystemAdapter('var', PortableVisibilityConverter::fromArray($permissionMap)),
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

    public function test_create_adapter_with_default_dir_visibility(): void
    {
        $factory = new LocalAdapterFactory();

        $supportedVisibilities = [Visibility::PUBLIC, Visibility::PRIVATE];
        foreach ($supportedVisibilities as $visibility) {
            $adapter = $factory->createAdapter("local://var?default_dir_visibility={$visibility}");

            $this->assertEquals(
                new LocalFilesystemAdapter('var', PortableVisibilityConverter::fromArray([], $visibility)),
                $adapter
            );
        }
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

    /**
     * @dataProvider create_adapter_throws_exception_when_permission_parameter_is_invalid_data_provider
     */
    public function test_create_adapter_throws_exception_when_permission_parameter_is_invalid(string $parameter, string $permission): void
    {
        $factory = new LocalAdapterFactory();

        try {
            $factory->createAdapter("local://var?{$parameter}={$permission}");
            $this->fail(sprintf(
                'Failed asserting that exception of type "%s" is thrown.',
                UnableToCreateAdapterException::class
            ));
        } catch (UnableToCreateAdapterException) {
            $this->addToAssertionCount(1);
        } catch (\Throwable $t) {
            $this->fail(sprintf(
                'Failed asserting that exception of type "%s" matches expected exception "%s".',
                get_class($t),
                UnableToCreateAdapterException::class
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
        $factory = new LocalAdapterFactory();

        $this->expectException(UnableToCreateAdapterException::class);

        $factory->createAdapter('local://var?default_dir_visibility=0755');
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
