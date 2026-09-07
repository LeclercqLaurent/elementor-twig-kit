<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Config;

/**
 * Configuration validée du plugin.
 *
 * Le point important est le mode dégradé : sans API configurée, le plugin ne
 * plante pas et ne laisse pas non plus une page à moitié rendue. Il bascule
 * sur le jeu de démonstration embarqué et le signale. Sur un site en
 * production, un plugin qui casse la page d'accueil parce qu'une clé manque
 * coûte plus cher que la fonctionnalité qu'il apporte.
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
