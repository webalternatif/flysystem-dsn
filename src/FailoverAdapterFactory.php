<?php

declare(strict_types=1);

namespace Webf\Flysystem\Dsn;

use Nyholm\Dsn\DsnParser;
use Nyholm\Dsn\Exception\InvalidDsnException as NyholmInvalidDsnException;
use Webf\Flysystem\Dsn\Exception\InvalidDsnException;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;
use Webf\Flysystem\Dsn\Exception\UnsupportedDsnException;
use Webf\FlysystemFailoverBundle\Flysystem\FailoverAdapter;
use Webf\FlysystemFailoverBundle\Flysystem\InnerAdapter;
use Webf\FlysystemFailoverBundle\MessageRepository\MessageRepositoryInterface;

class FailoverAdapterFactory implements FlysystemAdapterFactoryInterface
{
    public function __construct(
        private FlysystemAdapterFactoryInterface $adapterFactory,
        private MessageRepositoryInterface $messageRepository,
    ) {
    }

    public function createAdapter(string $dsn): FailoverAdapter
    {
        $dsnString = $dsn;
        try {
            $dsn = DsnParser::parseFunc($dsn);
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        if ('failover' !== $dsn->getName()) {
            throw UnsupportedDsnException::create($this, $dsnString);
        }

        if (!is_string($name = $dsn->getParameter('name'))) {
            throw MissingDsnParameterException::create('name', $dsnString);
        }

        if (count($arguments = $dsn->getArguments()) < 2) {
            throw MissingDsnParameterException::create('second argument', $dsnString);
        }

        $adapters = [];
        foreach ($arguments as $dsn) {
            $options = [];

            /** @var string|null $timeShift */
            $timeShift = $dsn->getParameter('time_shift');
            if (is_string($timeShift)) {
                $options['time_shift'] = (int) $timeShift;
            }

            $adapters[] = new InnerAdapter(
                $this->adapterFactory->createAdapter(
                    $dsn
                        ->withoutParameter('time_shift')
                        ->__toString()
                ),
                $options
            );
        }

        return new FailoverAdapter($name, $adapters, $this->messageRepository);
    }

    public function supports(string $dsn): bool
    {
        try {
            $name = DsnParser::parseFunc($dsn)->getName() ?: '';
        } catch (NyholmInvalidDsnException $e) {
            throw new InvalidDsnException($e->getMessage(), previous: $e);
        }

        return 'failover' === $name;
    }
}
