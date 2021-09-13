<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use OpenStack\OpenStack;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\Dsn\OpenStackSwiftAdapterFactory;
use Webf\Flysystem\OpenStackSwift\OpenStackSwiftAdapter;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\OpenStackSwiftAdapterFactory
 */
class OpenStackSwiftAdapterFactoryTest extends TestCase
{
    /**
     * @dataProvider create_adapter_data_provider
     */
    public function test_create_adapter(string $dsn, array $options, string $container): void
    {
        $factory = new OpenStackSwiftAdapterFactory();
        $adapter = $factory->createAdapter($dsn);

        $this->assertEquals(
            new OpenStackSwiftAdapter(new OpenStack($options), $container),
            $adapter
        );
    }

    public function create_adapter_data_provider(): iterable
    {
        yield 'minimal' => [
            'swift://username:password@host?region=region&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
            ],
            'container',
        ];

        yield 'with path' => [
            'swift://username:password@host/path?region=region&container=container',
            [
                'authUrl' => 'https://host/path',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
            ],
            'container',
        ];

        yield 'with http' => [
            'swift+http://username:password@host?region=region&container=container',
            [
                'authUrl' => 'http://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
            ],
            'container',
        ];

        yield 'with user id' => [
            'swift://:password@host?user_id=user_id&region=region&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'id' => 'user_id',
                    'password' => 'password',
                ],
            ],
            'container',
        ];

        yield 'with user domain id' => [
            'swift://username:password@host?region=region&user_domain_id=user_domain_id&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'user_domain_id'],
                ],
            ],
            'container',
        ];

        yield 'with user domain name' => [
            'swift://username:password@host?region=region&user_domain_name=user_domain_name&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['name' => 'user_domain_name'],
                ],
            ],
            'container',
        ];

        yield 'with domain id' => [
            'swift://username:password@host?region=region&domain_id=domain_id&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
                'scope' => [
                    'domain' => ['id' => 'domain_id'],
                ],
            ],
            'container',
        ];

        yield 'with domain name' => [
            'swift://username:password@host?region=region&domain_name=domain_name&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
                'scope' => [
                    'domain' => ['name' => 'domain_name'],
                ],
            ],
            'container',
        ];

        yield 'with project id' => [
            'swift://username:password@host?region=region&project_id=project_id&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
                'scope' => [
                    'project' => ['id' => 'project_id'],
                ],
            ],
            'container',
        ];

        yield 'with project name' => [
            'swift://username:password@host?region=region&project_name=project_name&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
                'scope' => [
                    'project' => ['name' => 'project_name'],
                ],
            ],
            'container',
        ];

        yield 'with project domain id' => [
            'swift://username:password@host?region=region&project_domain_id=project_domain_id&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
                'scope' => [
                    'project' => [
                        'domain' => ['id' => 'project_domain_id'],
                    ],
                ],
            ],
            'container',
        ];

        yield 'with project domain name' => [
            'swift://username:password@host?region=region&project_domain_name=project_domain_name&container=container',
            [
                'authUrl' => 'https://host',
                'region' => 'region',
                'user' => [
                    'name' => 'username',
                    'password' => 'password',
                    'domain' => ['id' => 'default'],
                ],
                'scope' => [
                    'project' => [
                        'domain' => ['name' => 'project_domain_name'],
                    ],
                ],
            ],
            'container',
        ];
    }

    /**
     * @dataProvider create_adapter_with_missing_parameter_data_provider
     */
    public function test_create_adapter_with_missing_parameter(string $dsn): void
    {
        $factory = new OpenStackSwiftAdapterFactory();

        $this->expectException(MissingDsnParameterException::class);

        $factory->createAdapter($dsn);
    }

    public function create_adapter_with_missing_parameter_data_provider(): iterable
    {
        yield 'username' => ['swift://:password@host?container=container&region=region'];
        yield 'region' => ['swift://username:password@host?container=container'];
        yield 'container' => ['swift://username:password@host?region=region'];
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new OpenStackSwiftAdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new OpenStackSwiftAdapterFactory();

        $unsupportedSchemes = ['kotlin', '-swift', 'swift-', '-swift+http', 'swift+http-'];

        foreach ($unsupportedSchemes as $scheme) {
            try {
                $factory->createAdapter("{$scheme}://username:password@host?region=region&container=container");
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
        $factory = new OpenStackSwiftAdapterFactory();

        $supportedSchemes = ['swift', 'swift+http', 'swift+https'];
        foreach ($supportedSchemes as $scheme) {
            $this->assertTrue($factory->supports("{$scheme}://username:password@host?region=region&container=container"));
        }

        $unsupportedSchemes = ['kotlin', 'swift+ssh', 'sswift', 'swiftt', 'sswift+http', 'swift+httpp', 'sswift+https', 'swift+httpss'];
        foreach ($unsupportedSchemes as $scheme) {
            $this->assertFalse($factory->supports("{$scheme}://username:password@host?region=region&container=container"));
        }
        $this->assertFalse($factory->supports('swift(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new OpenStackSwiftAdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->supports('Invalid DSN');
    }
}
