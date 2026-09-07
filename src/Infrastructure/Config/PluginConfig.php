<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Config;

/**
 * The plugin's validated configuration.
 *
 * What matters here is the degraded mode: with no API configured, the plugin
 * neither crashes nor leaves a half-rendered page. It falls back to the bundled
 * demo dataset and says so. On a production site, a plugin that breaks the home
 * page because one key is missing costs more than the feature it brings.
 */
final readonly class PluginConfig
{
    private const REQUIRED_IN_LIVE_MODE = ['JOBS_API_URL', 'JOBS_API_TOKEN'];

    public function __construct(
        public bool $demoMode,
        public string $apiUrl,
        public string $apiToken,
        public int $timeoutSeconds,
        public int $cacheTtlSeconds,
        public bool $debug,
    ) {
    }

    public static function fromEnvironment(Environment $environment): self
    {
        $demoMode = $environment->bool('JOBS_DEMO_MODE', true);

        if (!$demoMode) {
            self::assertLiveKeysPresent($environment);
        }

        return new self(
            demoMode: $demoMode,
            apiUrl: rtrim($environment->get('JOBS_API_URL'), '/'),
            apiToken: $environment->get('JOBS_API_TOKEN'),
            timeoutSeconds: max(1, (int) $environment->get('JOBS_API_TIMEOUT', '5')),
            cacheTtlSeconds: max(0, (int) $environment->get('JOBS_CACHE_TTL', '300')),
            debug: $environment->bool('JOBS_DEBUG'),
        );
    }

    private static function assertLiveKeysPresent(Environment $environment): void
    {
        $missing = array_values(array_filter(
            self::REQUIRED_IN_LIVE_MODE,
            static fn (string $key): bool => '' === $environment->get($key),
        ));

        if ([] !== $missing) {
            throw MissingConfiguration::keys($missing);
        }
    }
}
