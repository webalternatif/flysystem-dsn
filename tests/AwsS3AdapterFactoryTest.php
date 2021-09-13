<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn;

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\AwsS3AdapterFactory;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\AwsS3AdapterFactory
 */
class AwsS3AdapterFactoryTest extends TestCase
{
    /**
     * @dataProvider create_adapter_data_provider
     */
    public function test_create_adapter(string $dsn, array $options, string $bucket): void
    {
        $factory = new AwsS3AdapterFactory();
        $adapter = $factory->createAdapter($dsn);

        $expectedAdapter = new AwsS3V3Adapter(
            $expectedClient = new S3Client($options),
            $bucket
        );

        $this->assertEquals($expectedAdapter, $adapter);
        $this->assertEquals(
            $expectedClient->getCredentials(),
            $this->getS3Client($adapter)->getCredentials()
        );
    }

    public function create_adapter_data_provider(): iterable
    {
        yield 'minimal' => [
            's3://username:password@host?region=region&bucket=bucket',
            [
                'credentials' => [
                    'key' => 'username',
                    'secret' => 'password',
                ],
                'endpoint' => 'https://host',
                'region' => 'region',
                'version' => 'latest',
            ],
            'bucket',
        ];

        yield 'with path' => [
            's3://username:password@host/path?region=region&bucket=bucket',
            [
                'credentials' => [
                    'key' => 'username',
                    'secret' => 'password',
                ],
                'endpoint' => 'https://host/path',
                'region' => 'region',
                'version' => 'latest',
            ],
            'bucket',
        ];

        yield 'with http' => [
            's3+http://username:password@host?region=region&bucket=bucket',
            [
                'credentials' => [
                    'key' => 'username',
                    'secret' => 'password',
                ],
                'endpoint' => 'http://host',
                'region' => 'region',
                'version' => 'latest',
            ],
            'bucket',
        ];

        yield 'with version' => [
            's3://username:password@host?region=region&bucket=bucket&version=2006-03-01',
            [
                'credentials' => [
                    'key' => 'username',
                    'secret' => 'password',
                ],
                'endpoint' => 'https://host',
                'region' => 'region',
                'version' => '2006-03-01',
            ],
            'bucket',
        ];
    }

    /**
     * @dataProvider create_adapter_with_missing_parameter_data_provider
     */
    public function test_create_adapter_with_missing_parameter(string $dsn): void
    {
        $factory = new AwsS3AdapterFactory();

        $this->expectException(MissingDsnParameterException::class);

        $factory->createAdapter($dsn);
    }

    public function create_adapter_with_missing_parameter_data_provider(): iterable
    {
        yield 'region' => ['s3://username:password@host?bucket=bucket'];
        yield 'bucket' => ['s3://username:password@host?region=region'];
    }

    public function test_create_adapter_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new AwsS3AdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->createAdapter('Invalid DSN');
    }

    public function test_create_adapter_throws_exception_when_dsn_is_not_supported(): void
    {
        $factory = new AwsS3AdapterFactory();

        $unsupportedSchemes = ['s4', '-s3', 's3-', '-s3+http', 's3+http-'];

        foreach ($unsupportedSchemes as $scheme) {
            try {
                $factory->createAdapter("{$scheme}://username:password@host?region=region&bucket=bucket");
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
        $factory = new AwsS3AdapterFactory();

        $supportedSchemes = ['s3', 's3+http', 's3+https'];
        foreach ($supportedSchemes as $scheme) {
            $this->assertTrue($factory->supports("{$scheme}://username:password@host?region=region&bucket=bucket"));
        }

        $unsupportedSchemes = ['s4', 's3+ssh', 'ss3', 's33', 'ss3+http', 's3+httpp', 'ss3+https', 's3+httpss'];
        foreach ($unsupportedSchemes as $scheme) {
            $this->assertFalse($factory->supports("{$scheme}://username:password@host?region=region&bucket=bucket"));
        }
        $this->assertFalse($factory->supports('s3(inner://)'));
    }

    public function test_supports_throws_exception_when_dsn_is_invalid(): void
    {
        $factory = new AwsS3AdapterFactory();

        $this->expectException(InvalidDsnException::class);

        $factory->supports('Invalid DSN');
    }

    private function getS3Client(AwsS3V3Adapter $adapter): S3ClientInterface
    {
        $class = new \ReflectionClass(AwsS3V3Adapter::class);

        $clientProperty = $class->getProperty('client');
        $clientProperty->setAccessible(true);

        return $clientProperty->getValue($adapter);
    }
}
