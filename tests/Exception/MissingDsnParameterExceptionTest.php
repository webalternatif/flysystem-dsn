<?php

declare(strict_types=1);

namespace Tests\Webf\Flysystem\Dsn\Exception;

use Nyholm\Dsn\DsnParser;
use PHPUnit\Framework\TestCase;
use Webf\Flysystem\Dsn\Exception\MissingDsnParameterException;

/**
 * @internal
 * @covers \Webf\Flysystem\Dsn\Exception\MissingDsnParameterException
 */
class MissingDsnParameterExceptionTest extends TestCase
{
    public function test_constructor_is_private(): void
    {
        $class = new \ReflectionClass(MissingDsnParameterException::class);
        $this->assertTrue($class->getConstructor()->isPrivate());
    }

    public function test_missing_parameter_and_dsn_are_present_in_message(): void
    {
        $parameter = 'foo';
        $dsn = 'scheme://username:password@host/path?parameter=value';

        $exception = MissingDsnParameterException::create(
            $parameter,
            DsnParser::parse($dsn)
        );

        $this->assertStringContainsString($parameter, $exception->getMessage());
        $this->assertStringContainsString($dsn, $exception->getMessage());
    }
}
