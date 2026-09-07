<?php

declare(strict_types=1);

namespace ElementorTwigKit\WordPress;

use ElementorTwigKit\Domain\Port\JobOfferSource;
use ElementorTwigKit\Infrastructure\Config\PluginConfig;
use ElementorTwigKit\Infrastructure\Logging\Logger;
use ElementorTwigKit\Infrastructure\Rendering\Renderer;
use RuntimeException;

/**
 * Racine de composition, exposée statiquement, et c'est un compromis assumé.
 *
 * Elementor instancie lui-même les classes de widgets, sans argument, à chaque
 * rendu : on ne peut donc pas leur injecter leurs dépendances par le
 * constructeur. Les deux issues sont un localisateur de services ou des
 * fonctions globales ; le localisateur au moins nomme ses dépendances, se
 * remplace intégralement dans un test, et confine la contrainte à cette seule
 * classe. Tout le reste du plugin reçoit ses collaborateurs par injection.
 */
final class Services
{
    private static ?self $instance = null;

    public function __construct(
        public readonly JobOfferSource $offers,
        public readonly Renderer $renderer,
        public readonly PluginConfig $config,
        public readonly Logger $logger,
    ) {
    }

    public static function set(self $services): void
    {
        self::$instance = $services;
    }

    public static function get(): self
    {
        if (!self::$instance instanceof self) {
            throw new RuntimeException('Services non initialisés : Plugin::boot() n\'a pas été appelé.');
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
