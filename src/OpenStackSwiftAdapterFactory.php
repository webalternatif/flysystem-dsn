<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use Nyholm\Dsn\Configuration\Dsn;
use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use OpenStack\OpenStack;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\Flysystem\OpenStackSwift\OpenStackSwiftAdapter;

class OpenStackSwiftAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function createAdapter(string $dsn): OpenStackSwiftAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parse($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        $matches = [];
        if (1 !== preg_match('/^swift(?:\+(https?))?$/', $dsn->getScheme() ?: '', $matches)) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        if (!is_string($region = $dsn->getParameter('region'))) {
            throw MissingDsnParameterException::create('region', $dsnString);
        }

        if (!is_string($container = $dsn->getParameter('container'))) {
            throw MissingDsnParameterException::create('container', $dsnString);
        }

        $userId = $this->getStringParameter($dsn, 'user_id');
        $userDomainId = $this->getStringParameter($dsn, 'user_domain_id');
        $userDomainName = $this->getStringParameter($dsn, 'user_domain_name');
        $domainId = $this->getStringParameter($dsn, 'domain_id');
        $domainName = $this->getStringParameter($dsn, 'domain_name');
        $projectId = $this->getStringParameter($dsn, 'project_id');
        $projectName = $this->getStringParameter($dsn, 'project_name');
        $projectDomainId = $this->getStringParameter($dsn, 'project_domain_id');
        $projectDomainName = $this->getStringParameter($dsn, 'project_domain_name');

        $options = [
            'authUrl' => sprintf(
                '%s://%s',
                $matches[1] ?? 'https',
                ($dsn->getHost() ?: '') . ($dsn->getPath() ?: '')
            ),
            'region' => $region,
            'user' => [
                'password' => $dsn->getPassword(),
            ],
        ];

        if (null !== ($userName = $dsn->getUser())) {
            $options['user']['name'] = $userName;
        } else {
            if (null === $userId) {
                throw MissingDsnParameterException::create('username or user_id', $dsnString);
            }
        }

        if (null !== $userId) {
            $options['user']['id'] = $userId;
        } elseif (null === $userDomainId && null === $userDomainName) {
            $options['user']['domain']['id'] = 'default';
        }

        if (null !== $userDomainId) {
            $options['user']['domain']['id'] = $userDomainId;
        }

        if (null !== $userDomainName) {
            $options['user']['domain']['name'] = $userDomainName;
        }

        if (null !== $domainId) {
            $options['scope']['domain']['id'] = $domainId;
        }

        if (null !== $domainName) {
            $options['scope']['domain']['name'] = $domainName;
        }

        if (null !== $projectId) {
            $options['scope']['project']['id'] = $projectId;
        }

        if (null !== $projectName) {
            $options['scope']['project']['name'] = $projectName;
        }

        if (null !== $projectDomainId) {
            $options['scope']['project']['domain']['id'] = $projectDomainId;
        }

        if (null !== $projectDomainName) {
            $options['scope']['project']['domain']['name'] = $projectDomainName;
        }

        return new OpenStackSwiftAdapter(new OpenStack($options), $container);
    }

    public function supports(string $dsn): bool
    {
        try {
            $scheme = DsnParser::parse($dsn)->getScheme() ?: '';
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        return 1 === preg_match('/^swift(?:\+(https?))?$/', $scheme);
    }

    private function getStringParameter(Dsn $dsn, string $parameter): ?string
    {
        if (!is_string($value = $dsn->getParameter($parameter))) {
            return null;
        }

        return $value;
    }
}
