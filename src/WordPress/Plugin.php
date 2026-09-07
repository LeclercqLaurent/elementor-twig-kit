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
 * Plugin bootstrap.
 *
 * All object construction happens here and nowhere else, which is what lets the
 * rest of the code depend on interfaces only, and therefore be tested with
 * neither WordPress nor a network.
 *
 * The design point to remember is failure: an incomplete configuration
 * **disables the widgets and logs**, it never raises a fatal error. A plugin
 * that interrupts the rendering of a production site because one key is missing
 * does more damage than the feature it brings.
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
            $logger->warning('Widgets disabled. ' . $exception->getMessage());

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
