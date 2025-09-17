<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn\AdapterFactory;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\FunctionsNotAllowedException;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\DsnException;
use Webf\Flysystem\Dsn\Exception\DsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;

final class AwsS3AdapterFactory implements FlysystemAdapterFactoryInterface
{
    #[\Override]
    public function createAdapter(string $dsn): AwsS3V3Adapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        $matches = [];
        if (1 !== preg_match('/^s3(?:\+(https?))?$/', $dsn->getScheme() ?? '', $matches)) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        if (!is_string($region = $dsn->getParameter('region'))) {
            throw DsnParameterException::missingParameter('region', $dsnString);
        }

        if (!is_string($bucket = $dsn->getParameter('bucket'))) {
            throw DsnParameterException::missingParameter('bucket', $dsnString);
        }

        $clientParameters = [
            'endpoint' => sprintf(
                '%s://%s',
                $matches[1] ?? 'https',
                ($dsn->getHost() ?? '') . ($dsn->getPath() ?? '')
            ),
            'region' => $region,
            'version' => $dsn->getParameter('version', 'latest'),
        ];

        if (!is_null($dsn->getUser()) && !is_null($dsn->getPassword())) {
            $clientParameters['credentials'] = [
                'key' => $dsn->getUser(),
                'secret' => $dsn->getPassword(),
            ];
        }

        return new AwsS3V3Adapter(
            new S3Client(
                $clientParameters,
            ),
            $bucket
        );
    }

    #[\Override]
    public function supports(string $dsn): bool
    {
        try {
            $scheme = DsnParser::parse($dsn)->getScheme() ?? '';
        } catch (FunctionsNotAllowedException) {
            return false;
        } catch (NyholmInvalidDsnException $e) {
            throw new DsnException($e->getMessage(), previous: $e);
        }

        return 1 === preg_match('/^s3(?:\+(https?))?$/', $scheme);
    }
}
