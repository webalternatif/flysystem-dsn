<?php

namespace Webf\Flysystem\Dsn\Adapter;

use League\Flysystem\ChecksumAlgoIsNotSupported;
use League\Flysystem\ChecksumProvider;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
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
use Webf\Flysystem\Dsn\AdapterFactory\FlysystemAdapterFactoryInterface;
use Webf\Flysystem\Dsn\Exception\ExceptionInterface;

final class LazyAdapter implements FilesystemAdapter, ChecksumProvider, TemporaryUrlGenerator, PublicUrlGenerator
{
    private ?FilesystemAdapter $innerAdapter = null;

    public function __construct(
        private readonly FlysystemAdapterFactoryInterface $adapterFactory,
        private readonly string $dsn,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    private function getInnerAdapter(): FilesystemAdapter
    {
        if (null === $this->innerAdapter) {
            $this->innerAdapter = $this->adapterFactory->createAdapter($this->dsn);
        }

        return $this->innerAdapter;
    }

    #[\Override]
    public function fileExists(string $path): bool
    {
        try {
            return $this->getInnerAdapter()->fileExists($path);
        } catch (ExceptionInterface $e) {
            throw UnableToCheckFileExistence::forLocation($path, $e);
        }
    }

    #[\Override]
    public function directoryExists(string $path): bool
    {
        try {
            return $this->getInnerAdapter()->directoryExists($path);
        } catch (ExceptionInterface $e) {
            throw UnableToCheckDirectoryExistence::forLocation($path, $e);
        }
    }

    #[\Override]
    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $this->getInnerAdapter()->write($path, $contents, $config);
        } catch (ExceptionInterface $e) {
            throw UnableToWriteFile::atLocation($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function writeStream(string $path, $contents, Config $config): void
    {
        try {
            $this->getInnerAdapter()->writeStream($path, $contents, $config);
        } catch (ExceptionInterface $e) {
            throw UnableToWriteFile::atLocation($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function read(string $path): string
    {
        try {
            return $this->getInnerAdapter()->read($path);
        } catch (ExceptionInterface $e) {
            throw UnableToReadFile::fromLocation($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function readStream(string $path)
    {
        try {
            return $this->getInnerAdapter()->readStream($path);
        } catch (ExceptionInterface $e) {
            throw UnableToReadFile::fromLocation($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function delete(string $path): void
    {
        try {
            $this->getInnerAdapter()->delete($path);
        } catch (ExceptionInterface $e) {
            throw UnableToDeleteFile::atLocation($path, '=The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function deleteDirectory(string $path): void
    {
        try {
            $this->getInnerAdapter()->deleteDirectory($path);
        } catch (ExceptionInterface $e) {
            throw UnableToDeleteDirectory::atLocation($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function createDirectory(string $path, Config $config): void
    {
        try {
            $this->getInnerAdapter()->createDirectory($path, $config);
        } catch (ExceptionInterface $e) {
            throw UnableToCreateDirectory::atLocation($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function setVisibility(string $path, string $visibility): void
    {
        try {
            $this->getInnerAdapter()->setVisibility($path, $visibility);
        } catch (ExceptionInterface $e) {
            throw UnableToSetVisibility::atLocation($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function visibility(string $path): FileAttributes
    {
        try {
            return $this->getInnerAdapter()->visibility($path);
        } catch (ExceptionInterface $e) {
            throw UnableToRetrieveMetadata::visibility($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function mimeType(string $path): FileAttributes
    {
        try {
            return $this->getInnerAdapter()->mimeType($path);
        } catch (ExceptionInterface $e) {
            throw UnableToRetrieveMetadata::mimeType($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function lastModified(string $path): FileAttributes
    {
        try {
            return $this->getInnerAdapter()->lastModified($path);
        } catch (ExceptionInterface $e) {
            throw UnableToRetrieveMetadata::lastModified($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function fileSize(string $path): FileAttributes
    {
        try {
            return $this->getInnerAdapter()->fileSize($path);
        } catch (ExceptionInterface $e) {
            throw UnableToRetrieveMetadata::fileSize($path, 'The inner adapter failed to build.', $e);
        }
    }

    #[\Override]
    public function listContents(string $path, bool $deep): iterable
    {
        try {
            return $this->getInnerAdapter()->listContents($path, $deep);
        } catch (ExceptionInterface $e) {
            throw UnableToListContents::atLocation($path, $deep, $e);
        }
    }

    #[\Override]
    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->getInnerAdapter()->move($source, $destination, $config);
        } catch (ExceptionInterface $e) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $e);
        }
    }

    #[\Override]
    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->getInnerAdapter()->copy($source, $destination, $config);
        } catch (ExceptionInterface $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $e);
        }
    }

    #[\Override]
    public function checksum(string $path, Config $config): string
    {
        try {
            $innerAdapter = $this->getInnerAdapter();
        } catch (ExceptionInterface $e) {
            throw new UnableToProvideChecksum('The inner adapter failed to build.', $path, $e);
        }

        if ($innerAdapter instanceof ChecksumProvider) {
            return $innerAdapter->checksum($path, $config);
        }

        throw new ChecksumAlgoIsNotSupported('The inner adapter does not support checksums.');
    }

    #[\Override]
    public function publicUrl(string $path, Config $config): string
    {
        try {
            $innerAdapter = $this->getInnerAdapter();
        } catch (ExceptionInterface $e) {
            throw new UnableToGeneratePublicUrl('The inner adapter failed to build.', $path, $e);
        }

        if ($innerAdapter instanceof PublicUrlGenerator) {
            return $innerAdapter->publicUrl($path, $config);
        }

        throw UnableToGeneratePublicUrl::noGeneratorConfigured($path);
    }

    #[\Override]
    public function temporaryUrl(string $path, \DateTimeInterface $expiresAt, Config $config): string
    {
        try {
            $innerAdapter = $this->getInnerAdapter();
        } catch (ExceptionInterface $e) {
            throw new UnableToGenerateTemporaryUrl('The inner adapter failed to build.', $path, $e);
        }

        if ($innerAdapter instanceof TemporaryUrlGenerator) {
            return $innerAdapter->temporaryUrl($path, $expiresAt, $config);
        }

        throw UnableToGenerateTemporaryUrl::noGeneratorConfigured($path);
    }
}
