<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Infrastructure\Config\Environment;
use ElementorTwigKit\Infrastructure\Config\MissingConfiguration;
use ElementorTwigKit\Infrastructure\Config\PluginConfig;
use PHPUnit\Framework\TestCase;

final class PluginConfigTest extends TestCase
{
    public function testSansConfigurationLePluginDemarreEnModeDemonstration(): void
    {
        $config = PluginConfig::fromEnvironment(Environment::fromArray([]));

        self::assertTrue($config->demoMode);
        self::assertSame(5, $config->timeoutSeconds);
        self::assertSame(300, $config->cacheTtlSeconds);
        self::assertFalse($config->debug);
    }

    public function testEnModeReelLesClesVitalesManquantesSontNommees(): void
    {
        $this->expectException(MissingConfiguration::class);
        $this->expectExceptionMessage('JOBS_API_URL, JOBS_API_TOKEN');

        PluginConfig::fromEnvironment(Environment::fromArray(['JOBS_DEMO_MODE' => 'false']));
    }

    public function testEnModeReelUneSeuleCleManquanteSuffitAEchouer(): void
    {
        $this->expectException(MissingConfiguration::class);
        $this->expectExceptionMessage('JOBS_API_TOKEN');

        PluginConfig::fromEnvironment(Environment::fromArray([
            'JOBS_DEMO_MODE' => 'false',
            'JOBS_API_URL' => 'https://api.example.invalid',
        ]));
    }

    public function testLUrlDApiPerdSonSlashFinalEtLesDelaisOntUnPlancher(): void
    {
        $config = PluginConfig::fromEnvironment(Environment::fromArray([
            'JOBS_DEMO_MODE' => 'false',
            'JOBS_API_URL' => 'https://api.example.invalid/v1/',
            'JOBS_API_TOKEN' => 'jeton',
            'JOBS_API_TIMEOUT' => '0',
            'JOBS_CACHE_TTL' => '-10',
            'JOBS_DEBUG' => 'true',
        ]));

        self::assertFalse($config->demoMode);
        self::assertSame('https://api.example.invalid/v1', $config->apiUrl);
        self::assertSame(1, $config->timeoutSeconds);
        self::assertSame(0, $config->cacheTtlSeconds);
        self::assertTrue($config->debug);
    }
}
