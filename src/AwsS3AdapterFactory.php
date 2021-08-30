<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

class AwsS3AdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function createAdapter(string $dsn): AwsS3V3Adapter
    {
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        $matches = [];
        if (1 !== preg_match('/^s3(?:\+(https?))?$/', $dsn->getScheme() ?: '', $matches)) {
            throw UnsupportedDsnException::create($this, $dsn);
        }

        if (!is_string($region = $dsn->getParameter('region'))) {
            throw MissingDsnParameterException::create('region', $dsn);
        }

        if (!is_string($bucket = $dsn->getParameter('bucket'))) {
            throw MissingDsnParameterException::create('bucket', $dsn);
        }

        return new AwsS3V3Adapter(
            new S3Client(
                [
                    'credentials' => [
                        'key' => $dsn->getUser(),
                        'secret' => $dsn->getPassword(),
                    ],
                    'endpoint' => sprintf(
                        '%s://%s',
                        $matches[1] ?? 'https',
                        ($dsn->getHost() ?: '') . ($dsn->getPath() ?: '')
                    ),
                    'region' => $region,
                    'version' => $dsn->getParameter('version', 'latest'),
                ],
            ),
            $bucket
        );
    }

    public function supports(string $dsn): bool
    {
        try {
            $scheme = DsnParser::parse($dsn)->getScheme() ?: '';
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        return 1 === preg_match('/^s3(?:\+(https?))?$/', $scheme);
    }
}
