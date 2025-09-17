<?php

namespace Tests\Webf\Flysystem\Dsn\Adapter;

use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckDirectoryExistence;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToGeneratePublicUrl;
use League\Flysystem\UnableToGenerateTemporaryUrl;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToProvideChecksum;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Adapter\LazyAdapter;
use Webf\Flysystem\Dsn\AdapterFactory\FlysystemAdapterFactoryInterface;
use Webf\Flysystem\Dsn\Exception\ExceptionInterface;

/**
 * @internal
 *
 * @covers \Webf\Flysystem\Dsn\Adapter\LazyAdapter
 */
class LazyAdapterTest extends TestCase
{
    /**
     * @dataProvider method_calls_data_provider
     */
    public function test_method_calls_are_forwarded_to_the_inner_adapter(
        string $method,
        array $arguments,
        string $exceptionClass,
        ?string $adapterInterface = FilesystemAdapter::class,
    ): void {
        $adapter = $this->createMock($adapterInterface);
        $adapter
            ->expects($this->once())
            ->method($method)
            ->with(...$arguments)
        ;

        $createAdapterHasBeenCalled = false;
        $adapterFactory = $this->createMock(FlysystemAdapterFactoryInterface::class);
        $adapterFactory
            ->expects($this->once())
            ->method('createAdapter')
            ->willReturnCallback(function () use ($adapter, &$createAdapterHasBeenCalled) {
                $createAdapterHasBeenCalled = true;

                return $adapter;
            })
        ;

        $lazyAdapter = new LazyAdapter($adapterFactory, 'stub://inner');
        $this->assertFalse($createAdapterHasBeenCalled);
        $lazyAdapter->{$method}(...$arguments);
        $this->assertTrue($createAdapterHasBeenCalled);
    }

    /**
     * @dataProvider method_calls_data_provider
     */
    public function test_exceptions_are_transformed_when_the_inner_adapter_failed_to_build(
        string $method,
        array $arguments,
        string $exceptionClass,
    ): void {
        $adapterFactory = $this->createMock(FlysystemAdapterFactoryInterface::class);
        $adapterFactory
            ->expects($this->once())
            ->method('createAdapter')
            ->willThrowException($this->createMock(ExceptionInterface::class))
        ;

        $lazyAdapter = new LazyAdapter($adapterFactory, 'stub://inner');

        $this->expectException($exceptionClass);
        $lazyAdapter->{$method}(...$arguments);
    }

    public function method_calls_data_provider(): iterable
    {
        yield 'fileExists' => ['fileExists', ['some/path'], UnableToCheckFileExistence::class];
        yield 'directoryExists' => ['directoryExists', ['some/path'], UnableToCheckDirectoryExistence::class];
        yield 'write' => ['write', ['some/path', 'some contents', new Config()], UnableToWriteFile::class];
        yield 'writeStream' => ['writeStream', ['some/path', 'some contents', new Config()], UnableToWriteFile::class];
        yield 'read' => ['read', ['some/path'], UnableToReadFile::class];
        yield 'readStream' => ['readStream', ['some/path'], UnableToReadFile::class];
        yield 'delete' => ['delete', ['some/path'], UnableToDeleteFile::class];
        yield 'deleteDirectory' => ['deleteDirectory', ['some/path'], UnableToDeleteDirectory::class];
        yield 'createDirectory' => ['createDirectory', ['some/path', new Config()], UnableToCreateDirectory::class];
        yield 'setVisibility' => ['setVisibility', ['some/path', rand(0, 1) ? 'public' : 'private'], UnableToSetVisibility::class];
        yield 'visibility' => ['visibility', ['some/path'], UnableToRetrieveMetadata::class];
        yield 'mimeType' => ['mimeType', ['some/path'], UnableToRetrieveMetadata::class];
        yield 'lastModified' => ['lastModified', ['some/path'], UnableToRetrieveMetadata::class];
        yield 'fileSize' => ['fileSize', ['some/path'], UnableToRetrieveMetadata::class];
        yield 'listContents' => ['listContents', ['some/path', (bool) rand(0, 1)], UnableToListContents::class];
        yield 'move' => ['move', ['some/source', 'some/destination', new Config()], UnableToMoveFile::class];
        yield 'copy' => ['copy', ['some/source', 'some/destination', new Config()], UnableToCopyFile::class];
        yield 'checksum' => ['checksum', ['some/path', new Config()], UnableToProvideChecksum::class, ChecksumAdapter::class];
        yield 'publicUrl' => ['publicUrl', ['some/path', new Config()], UnableToGeneratePublicUrl::class, PublicUrlAdapter::class];
        yield 'temporaryUrl' => ['temporaryUrl', ['some/path', new \DateTime('now +10 minutes'), new Config()], UnableToGenerateTemporaryUrl::class, TemporaryUrlAdapter::class];
    }

    /**
     * @dataProvider extra_methods_data_provider
     */
    public function test_extra_methods_throw_exceptions_if_the_inner_adapter_does_not_implement_interfaces(string $method, array $arguments, string $exceptionClass): void
    {
        $adapterFactory = $this->createMock(FlysystemAdapterFactoryInterface::class);
        $adapterFactory
            ->expects($this->once())
            ->method('createAdapter')
            ->willReturn($this->createMock(FilesystemAdapter::class))
        ;

        $lazyAdapter = new LazyAdapter($adapterFactory, 'stub://inner');

        $this->expectException($exceptionClass);
        $lazyAdapter->{$method}(...$arguments);
    }

    public function extra_methods_data_provider(): iterable
    {
        yield 'checksum' => ['checksum', ['some/path', new Config()], ChecksumAlgoIsNotSupported::class];
        yield 'publicUrl' => ['publicUrl', ['some/path', new Config()], UnableToGeneratePublicUrl::class];
        yield 'temporaryUrl' => ['temporaryUrl', ['some/path', new \DateTime('now +10 minutes'), new Config()], UnableToGenerateTemporaryUrl::class];
    }
}

abstract class ChecksumAdapter implements FilesystemAdapter, ChecksumProvider
{
}

abstract class PublicUrlAdapter implements FilesystemAdapter, PublicUrlGenerator
{
}

abstract class TemporaryUrlAdapter implements FilesystemAdapter, TemporaryUrlGenerator
{
}
