<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Infrastructure\Config\Environment;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    private string $path = '';

    protected function setUp(): void
    {
        $directory = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__, 2), 'var', 'test']);

        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $this->path = $directory . DIRECTORY_SEPARATOR . uniqid('env-', true);
    }

    protected function tearDown(): void
    {
        if ('' !== $this->path && is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testAMissingFileYieldsAnEmptyEnvironmentRatherThanAnError(): void
    {
        $environment = Environment::fromFile($this->path . '-missing');

        self::assertSame('fallback', $environment->get('JOBS_API_URL', 'fallback'));
    }

    public function testCommentsBlankLinesAndQuotesAreIgnored(): void
    {
        file_put_contents($this->path, <<<'ENV'
            # a comment
            JOBS_API_URL="https://api.example.invalid"

            JOBS_API_TOKEN='secret'
            a line with no equals sign
            JOBS_DEBUG = true
            ENV);

        $environment = Environment::fromFile($this->path);

        self::assertSame('https://api.example.invalid', $environment->get('JOBS_API_URL'));
        self::assertSame('secret', $environment->get('JOBS_API_TOKEN'));
        self::assertTrue($environment->bool('JOBS_DEBUG'));
    }

    public function testAnEmptyValueFallsBackToTheDefault(): void
    {
        $environment = Environment::fromArray(['JOBS_API_URL' => '']);

        self::assertSame('default', $environment->get('JOBS_API_URL', 'default'));
    }

    public function testBooleansAcceptTheUsualSpellings(): void
    {
        $environment = Environment::fromArray([
            'A' => 'yes', 'B' => 'ON', 'C' => '1',
            'D' => 'no', 'E' => 'off', 'F' => '0',
            'G' => 'maybe',
        ]);

        foreach (['A', 'B', 'C'] as $key) {
            self::assertTrue($environment->bool($key), $key);
        }

        foreach (['D', 'E', 'F'] as $key) {
            self::assertFalse($environment->bool($key, true), $key);
        }

        self::assertTrue($environment->bool('G', true));
        self::assertFalse($environment->bool('MISSING'));
    }

    public function testDecimalsFallBackToTheDefaultWhenNotNumeric(): void
    {
        $environment = Environment::fromArray(['THRESHOLD' => '0.7', 'BROKEN' => 'plenty']);

        self::assertSame(0.7, $environment->float('THRESHOLD', 0.5));
        self::assertSame(0.5, $environment->float('BROKEN', 0.5));
    }
}
