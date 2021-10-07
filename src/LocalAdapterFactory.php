<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Nyholm\Dsn\Configuration\Dsn;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\FunctionsNotAllowedException;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\UnableToCreateAdapterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

class LocalAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function createAdapter(string $dsn): LocalFilesystemAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        if ('local' !== $dsn->getScheme()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        $publicFilePermission = $this->getPermissionParameter($dsn, 'public_file_permission');
        $privateFilePermission = $this->getPermissionParameter($dsn, 'private_file_permission');
        $publicDirPermission = $this->getPermissionParameter($dsn, 'public_dir_permission');
        $privateDirPermission = $this->getPermissionParameter($dsn, 'private_dir_permission');

        $defaultDirVisibility = $this->getStringParameter($dsn, 'default_dir_visibility') ?: Visibility::PRIVATE;
        if (!in_array($defaultDirVisibility, [Visibility::PUBLIC, Visibility::PRIVATE])) {
            throw UnableToCreateAdapterException::create(sprintf('Parameter "default_dir_visibility" must be "%s" or "%s"', Visibility::PUBLIC, Visibility::PRIVATE), $dsnString);
        }

        $permissionMap = [];
        if (null !== $publicFilePermission) {
            $permissionMap['file']['public'] = $publicFilePermission;
        }
        if (null !== $privateFilePermission) {
            $permissionMap['file']['private'] = $privateFilePermission;
        }
        if (null !== $publicDirPermission) {
            $permissionMap['dir']['public'] = $publicDirPermission;
        }
        if (null !== $privateDirPermission) {
            $permissionMap['dir']['private'] = $privateDirPermission;
        }

        return new LocalFilesystemAdapter(
            $this->decodePath(($dsn->getHost() ?: '') . ($dsn->getPath() ?: '')),
            PortableVisibilityConverter::fromArray($permissionMap, $defaultDirVisibility)
        );
    }

    public function supports(string $dsn): bool
    {
        try {
            $scheme = DsnParser::parse($dsn)->getScheme() ?: '';
        } catch (FunctionsNotAllowedException) {
            return false;
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        return 'local' === $scheme;
    }

    private function getStringParameter(Dsn $dsn, string $parameter): ?string
    {
        if (!is_string($value = $dsn->getParameter($parameter))) {
            return null;
        }

        return $value;
    }

    private function getPermissionParameter(Dsn $dsn, string $parameter): ?int
    {
        if (null === ($value = $this->getStringParameter($dsn, $parameter))) {
            return null;
        }

        if (!preg_match('/^[0-9]{3,4}$/', $value)) {
            throw UnableToCreateAdapterException::create(sprintf('Parameter "%s" must be of length 3 or 4', $parameter), $dsn->__toString());
        }

        return intval($value, 8);
    }

    private function decodePath(string $path): string
    {
        return str_replace('%20', ' ', $path);
    }
}
