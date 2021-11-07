<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Nyholm\Dsn\Configuration\Dsn;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\FunctionsNotAllowedException;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

class FtpAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public const TRANSFER_MODE_ASCII = 'ascii';
    public const TRANSFER_MODE_BINARY = 'binary';

    public const SYSTEM_TYPE_UNIX = 'unix';
    public const SYSTEM_TYPE_WINDOWS = 'windows';

    public function createAdapter(string $dsn): FtpAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        if ('ftp' !== $dsn->getScheme()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        $transferMode = $this->getStringParameter($dsn, 'transfer_mode') ?: self::TRANSFER_MODE_BINARY;
        if (!in_array($transferMode, [self::TRANSFER_MODE_ASCII, self::TRANSFER_MODE_BINARY])) {
            throw InvalidDsnParameterException::create(sprintf('must be "%s" or "%s"', self::TRANSFER_MODE_ASCII, self::TRANSFER_MODE_BINARY), 'transfer_mode', $dsnString);
        }
        $transferMode = self::TRANSFER_MODE_ASCII === $transferMode ? FTP_ASCII : FTP_BINARY;

        $systemType = $this->getStringParameter($dsn, 'system_type');
        if (null !== $systemType && !in_array($systemType, [self::SYSTEM_TYPE_UNIX, self::SYSTEM_TYPE_WINDOWS])) {
            throw InvalidDsnParameterException::create(sprintf('must be "%s" or "%s"', self::SYSTEM_TYPE_UNIX, self::SYSTEM_TYPE_WINDOWS), 'system_type', $dsnString);
        }

        $publicFilePermission = $this->getPermissionParameter($dsn, 'public_file_permission');
        $privateFilePermission = $this->getPermissionParameter($dsn, 'private_file_permission');
        $publicDirPermission = $this->getPermissionParameter($dsn, 'public_dir_permission');
        $privateDirPermission = $this->getPermissionParameter($dsn, 'private_dir_permission');

        $defaultDirVisibility = $this->getStringParameter($dsn, 'default_dir_visibility') ?: Visibility::PRIVATE;
        if (!in_array($defaultDirVisibility, [Visibility::PUBLIC, Visibility::PRIVATE])) {
            throw InvalidDsnParameterException::create(sprintf('must be "%s" or "%s"', Visibility::PUBLIC, Visibility::PRIVATE), 'default_dir_visibility', $dsnString);
        }

        $ignorePassiveAddress = $this->getStringParameter($dsn, 'ignore_passive_address');
        if (null !== $ignorePassiveAddress) {
            $ignorePassiveAddress = !('false' === $ignorePassiveAddress) && $ignorePassiveAddress;
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

        return new FtpAdapter(
            new FtpConnectionOptions(
                $dsn->getHost() ?: '',
                $this->decodePath($dsn->getPath() ?: '/'),
                $dsn->getUser() ?: '',
                $dsn->getPassword() ?: '',
                $dsn->getPort() ?: 21,
                $this->getBoolParameter($dsn, 'ssl', false),
                (int) ($this->getStringParameter($dsn, 'timeout') ?: 90),
                $this->getBoolParameter($dsn, 'utf8', false),
                $this->getBoolParameter($dsn, 'passive', true),
                $transferMode,
                $systemType,
                $ignorePassiveAddress,
                $this->getBoolParameter($dsn, 'timestamps_on_unix_listings', false), // bool $enableTimestampsOnUnixListings = false,
                $this->getBoolParameter($dsn, 'recurse_manually', false), // bool $recurseManually = false
            ),
            visibilityConverter: PortableVisibilityConverter::fromArray(
                $permissionMap,
                $defaultDirVisibility
            )
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

        return 'ftp' === $scheme;
    }

    private function getStringParameter(Dsn $dsn, string $parameter): ?string
    {
        if (!is_string($value = $dsn->getParameter($parameter))) {
            return null;
        }

        return $value;
    }

    private function getBoolParameter(Dsn $dsn, string $parameter, bool $default): bool
    {
        if (null === ($value = $this->getStringParameter($dsn, $parameter))) {
            return $default;
        }

        if ('false' === $value) {
            return false;
        }

        return (bool) $value;
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
