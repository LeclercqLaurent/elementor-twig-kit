<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Infrastructure\Logging\ErrorLogLogger;
use ElementorTwigKit\Infrastructure\Logging\NullLogger;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    public function testTheServerLogReceivesAPrefixedLine(): void
    {
        $directory = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'var', 'test']);

        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $file = $directory . DIRECTORY_SEPARATOR . 'error.log';
        $previous = (string) ini_get('error_log');

        ini_set('error_log', $file);

        try {
            (new ErrorLogLogger())->warning('API unreachable');
        } finally {
            ini_set('error_log', $previous);
        }

        self::assertStringContainsString('[elementor-twig-kit] API unreachable', (string) file_get_contents($file));

        unlink($file);
    }

    public function testTheNullLoggerDoesNothingAndRaisesNoError(): void
    {
        $this->expectNotToPerformAssertions();

        (new NullLogger())->warning('ignored');
    }
}
