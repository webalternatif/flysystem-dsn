<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\PhpseclibV2\SftpAdapter;
use League\Flysystem\PhpseclibV2\SftpConnectionProvider;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Nyholm\Dsn\Configuration\Dsn;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\FunctionsNotAllowedException;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

class SftpAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function createAdapter(string $dsn): SftpAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        if ('sftp' !== $dsn->getScheme()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        $publicFilePermission = $this->getPermissionParameter($dsn, 'public_file_permission');
        $privateFilePermission = $this->getPermissionParameter($dsn, 'private_file_permission');
        $publicDirPermission = $this->getPermissionParameter($dsn, 'public_dir_permission');
        $privateDirPermission = $this->getPermissionParameter($dsn, 'private_dir_permission');

        $defaultDirVisibility = $this->getStringParameter($dsn, 'default_dir_visibility') ?: Visibility::PRIVATE;
        if (!in_array($defaultDirVisibility, [Visibility::PUBLIC, Visibility::PRIVATE])) {
            throw InvalidDsnParameterException::create(sprintf('must be "%s" or "%s"', Visibility::PUBLIC, Visibility::PRIVATE), 'default_dir_visibility', $dsnString);
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

        return new SftpAdapter(
            new SftpConnectionProvider(
                $dsn->getHost() ?: '',
                $dsn->getUser() ?: '',
                $dsn->getPassword(),
                $this->getStringParameter($dsn, 'private_key'),
                $this->getStringParameter($dsn, 'passphrase'),
                $dsn->getPort() ?: 22,
                (bool) $this->getStringParameter($dsn, 'use_agent'),
                $this->getIntParameter($dsn, 'timeout') ?: 10,
                $this->getIntParameter($dsn, 'max_retries') ?: 4,
                $this->getStringParameter($dsn, 'host_fingerprint'),
            ),
            $this->decodePath($dsn->getPath() ?: '/'),
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

        return 'sftp' === $scheme;
    }

    private function getStringParameter(Dsn $dsn, string $parameter): ?string
    {
        if (!is_string($value = $dsn->getParameter($parameter))) {
            return null;
        }

        return $value;
    }

    private function getIntParameter(Dsn $dsn, string $parameter): ?int
    {
        if (null === ($value = $this->getStringParameter($dsn, $parameter))) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @throws InvalidDsnParameterException
     */
    private function getPermissionParameter(Dsn $dsn, string $parameter): ?int
    {
        if (null === ($value = $this->getStringParameter($dsn, $parameter))) {
            return null;
        }

        if (!preg_match('/^[0-9]{3,4}$/', $value)) {
            throw InvalidDsnParameterException::create('must be of length 3 or 4', $parameter, $dsn->__toString());
        }

        return intval($value, 8);
    }

    private function decodePath(string $path): string
    {
        return str_replace('%20', ' ', $path);
    }
}
