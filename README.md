# Flysystem DSN

[![Source code](https://img.shields.io/badge/source-GitHub-blue)](https://github.com/webalternatif/flysystem-dsn)
[![Packagist Version](https://img.shields.io/packagist/v/webalternatif/flysystem-dsn)](https://packagist.org/packages/webalternatif/flysystem-dsn)
[![Software license](https://img.shields.io/github/license/webalternatif/flysystem-dsn)](https://github.com/webalternatif/flysystem-dsn/blob/master/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/webalternatif/flysystem-dsn)](https://github.com/webalternatif/flysystem-dsn/issues) \
[![Test status](https://img.shields.io/github/workflow/status/webalternatif/flysystem-dsn/test?label=tests)](https://github.com/webalternatif/flysystem-dsn/actions/workflows/test.yml)
[![Psalm coverage](https://shepherd.dev/github/webalternatif/flysystem-dsn/coverage.svg)](https://psalm.dev)
[![Psalm level](https://shepherd.dev/github/webalternatif/flysystem-dsn/level.svg)](https://psalm.dev)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/webalternatif/flysystem-dsn/master)](https://infection.github.io)

A set of factories to build [Flysystem][1] adapters from DSN.

## Installation

```bash
$ composer require webalternatif/flysystem-dsn
```

Because this package does not explicitely require inner adapters, you will have
to `composer require` them yourself in your project.\
See the [adapters section](#adapters) to know how to install them.

## Usage

```php
use Webf\Flysystem\Dsn\AwsS3AdapterFactory;
use Webf\Flysystem\Dsn\FlysystemAdapterFactory;
use Webf\Flysystem\Dsn\OpenStackSwiftAdapterFactory;

$factory = new FlysystemAdapterFactory([
    new AwsS3AdapterFactory(),
    new OpenStackSwiftAdapterFactory(),
]);

$adapter = $factory->createAdapter($dsn);
```

## Adapters

### AWS S3

|               |                                                               |
|---------------|---------------------------------------------------------------|
| Inner adapter | [`league/flysystem-aws-s3-v3`][10]                            |
| Install       | `composer require league/flysystem-aws-s3-v3`                 |
| Factory class | `Webf\Flysystem\Dsn\AwsS3AdapterFactory`                      |
| DSN           | `s3://username:password@endpoint?region=region&bucket=bucket` |
|               |                                                               |

* Use `s3+http://` if the endpoint does not support https.
* `s3://` is equivalent to `s3+https://`.

#### Optional DSN parameters

* `version` (default: `latest`)

### Failover

|               |                                                            |
|---------------|------------------------------------------------------------|
| Inner adapter | [`webalternatif/flysystem-failover-bundle`][11]            |
| Install       | `composer require webalternatif/flysystem-failover-bundle` |
| Factory class | `Webf\Flysystem\Dsn\FailoverAdapterFactory`                |
| DSN           | `failover(inner1:// inner2:// ...)?name=name`              |
|               |                                                            |

* There must be at least 2 DSN arguments for the failover DSN function.
* The `name` parameter is used for the failover adapter's name in failover bundle (used to identify adapters in Symfony commands).
* For each inner DSN, you can specify a `time_shift` parameter (see [configuration section][111] of the failover bundle for more info). This parameter is removed from the inner DSN when it's built.

### In memory

|               |                                             |
|---------------|---------------------------------------------|
| Inner adapter | [`league/flysystem-memory`][12]             |
| Install       | `composer require league/flysystem-memory`  |
| Factory class | `Webf\Flysystem\Dsn\InMemoryAdapterFactory` |
| DSN           | `in-memory://`                              |
|               |                                             |

#### Optional DSN parameters

* `default_visibility`: default visibility of created files and directories (must be `public` or `private`, default: `public`)

### Local

|               |                                          |
|---------------|------------------------------------------|
| Inner adapter | Built-in with [`league/flysystem`][13]   |
| Factory class | `Webf\Flysystem\Dsn\LocalAdapterFactory` |
| DSN           | `local://absolute_or_relative_path`      |
|               |                                          |

* If path contains spaces, replace each one by `%20`.

#### Optional DSN parameters

* `public_file_permission`: unix permission for public files (default: `0644`)
* `private_file_permission`: unix permission for public files (default: `0600`)
* `public_dir_permission`: unix permission for public files (default: `0755`)
* `private_dir_permission`: unix permission for public files (default: `0700`)
* `default_dir_visibility`: default visibility for automatically created directories (must be `public` or `private`, default: `private`)

### OpenStack Swift

|               |                                                                        |
|---------------|------------------------------------------------------------------------|
| Inner adapter | [`webalternatif/flysystem-openstack-swift`][14]                        |
| Install       | `composer require webalternatif/flysystem-openstack-swift`             |
| Factory class | `Webf\Flysystem\Dsn\OpenStackSwiftAdapterFactory`                      |
| DSN           | `swift://username:password@endpoint?region=region&container=container` |
|               |                                                                        |

* Use `swift+http://` if the endpoint does not support https.
* `swift://` is equivalent to `swift+https://`.
* `username` is optional if parameter `user_id` is present.

#### Optional DSN parameters

* `user_id`: `auth.identity.password.user.id` value sent to Keystone v3 API
* `user_domain_id`: `auth.identity.password.user.domain.id` value sent to Keystone v3 API (default: `default` if `user_id` and `user_domain_name` are not defined)
* `user_domain_name`: `auth.identity.password.user.domain.name` value sent to Keystone v3 API
* `domain_id`: `auth.scope.domain.id` value sent to Keystone v3 API
* `domain_name`: `auth.scope.domain.name` value sent to Keystone v3 API
* `project_id`: `auth.scope.project.id` value sent to Keystone v3 API
* `project_name`: `auth.scope.project.name` value sent to Keystone v3 API
* `project_domain_id`: `auth.scope.project.domain.id` value sent to Keystone v3 API
* `project_domain_name`: `auth.scope.project.domain.name` value sent to Keystone v3 API

### Sftp

|               |                                                    |
|---------------|----------------------------------------------------|
| Inner adapter | [`league/flysystem-sftp`][15]                      |
| Install       | `composer require league/flysystem-sftp`           |
| Factory class | `Webf\Flysystem\Dsn\SftpAdapterFactory`            |
| DSN           | `sftp://username:password@host:port/absolute/path` |
|               |                                                    |

* The password can be empty if the `private_key` parameter is defined.
* If path contains spaces, replace each one by `%20`.

#### Optional DSN parameters

* `private_key`: absolute path of a private key file, can be used instead of password
* `passphrase`: passphrase of the private key
* `use_agent`: whether to use ssh agent or not (default: `false`)
* `timeout`: request timeout in seconds (default: `10`)
* `max_retries`: number of connection retries before triggering an error (default: `4`)
* `host_fingerprint`: the host fingerprint to check
* `public_file_permission`: unix permission for public files (default: `0644`)
* `private_file_permission`: unix permission for public files (default: `0600`)
* `public_dir_permission`: unix permission for public files (default: `0755`)
* `private_dir_permission`: unix permission for public files (default: `0700`)
* `default_dir_visibility`: default visibility for automatically created directories (must be `public` or `private`, default: `private`)

## Tests

To run all tests, execute the command:

```bash
composer test
```

This will run [Psalm][2], [PHPUnit][3], [Infection][4] and a [PHP-CS-Fixer][5]
check, but you can run them individually like this:

```bash
composer psalm
composer phpunit
composer infection
composer cs-check
```

[1]: https://flysystem.thephpleague.com
[2]: https://psalm.dev
[3]: https://phpunit.de
[4]: https://infection.github.io
[5]: https://cs.symfony.com/
[10]: https://github.com/thephpleague/flysystem-aws-s3-v3
[11]: https://github.com/webalternatif/flysystem-failover-bundle
[111]: https://github.com/webalternatif/flysystem-failover-bundle#configuration
[12]: https://github.com/thephpleague/flysystem-memory
[13]: https://github.com/thephpleague/flysystem
[14]: https://github.com/webalternatif/flysystem-openstack-swift
[15]: https://github.com/thephpleague/flysystem-sftp
