<?php

declare(strict_types=1);

namespace ElementorTwigKit\WordPress;

use Elementor\Widgets_Manager;
use ElementorTwigKit\Domain\Port\JobOfferSource;
use ElementorTwigKit\Infrastructure\Api\CurlHttpClient;
use ElementorTwigKit\Infrastructure\Api\HttpJobOfferSource;
use ElementorTwigKit\Infrastructure\Api\JobOfferMapper;
use ElementorTwigKit\Infrastructure\Config\Environment;
use ElementorTwigKit\Infrastructure\Config\MissingConfiguration;
use ElementorTwigKit\Infrastructure\Config\PluginConfig;
use ElementorTwigKit\Infrastructure\Fixture\FixtureJobOfferSource;
use ElementorTwigKit\Infrastructure\Logging\ErrorLogLogger;
use ElementorTwigKit\Infrastructure\Logging\Logger;
use ElementorTwigKit\Infrastructure\Rendering\TwigRenderer;
use ElementorTwigKit\WordPress\Widget\JobListWidget;
use ElementorTwigKit\WordPress\Widget\JobSearchWidget;

/**
 * Amorçage du plugin.
 *
 * Toute la construction d'objets a lieu ici et nulle part ailleurs : c'est ce
 * qui permet au reste du code de ne dépendre que d'interfaces, donc d'être
 * testé sans WordPress ni réseau.
 *
 * Le point de conception à retenir est l'échec : une configuration incomplète
 * **désactive les widgets et journalise**, elle ne lève pas d'erreur fatale. Un
 * plugin qui interrompt le rendu d'un site en production parce qu'une clé
 * manque cause plus de dégâts que la fonctionnalité qu'il apporte.
 */
final readonly class Plugin
{
    private const WIDGET_CLASSES = [
        JobListWidget::class,
        JobSearchWidget::class,
    ];

    public function __construct(private string $pluginDirectory)
    {
    }

    public function boot(): bool
    {
        $logger = new ErrorLogLogger();
        $config = $this->loadConfig($logger);

        if (!$config instanceof PluginConfig) {
            return false;
        }

        Services::set(new Services(
            offers: $this->buildSource($config, $logger),
            renderer: new TwigRenderer($this->pluginDirectory . '/templates', $logger, $config->debug),
            config: $config,
            logger: $logger,
        ));

        add_action('elementor/widgets/register', [$this, 'registerWidgets']);

        return true;
    }

    public function registerWidgets(Widgets_Manager $widgets): void
    {
        foreach (self::WIDGET_CLASSES as $class) {
            $widgets->register(new $class());
        }
    }

    private function loadConfig(Logger $logger): ?PluginConfig
    {
        try {
            return PluginConfig::fromEnvironment(Environment::fromFile($this->pluginDirectory . '/.env'));
        } catch (MissingConfiguration $exception) {
            $logger->warning('Widgets désactivés. ' . $exception->getMessage());

            return null;
        }
    }

    private function buildSource(PluginConfig $config, Logger $logger): JobOfferSource
    {
        $mapper = new JobOfferMapper($logger);

        if ($config->demoMode) {
            return new FixtureJobOfferSource($mapper, $this->pluginDirectory . '/resources/fixtures/job-offers.json');
        }

        return new HttpJobOfferSource(new CurlHttpClient(), $mapper, $config);
    }
}
