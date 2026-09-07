<?php

declare(strict_types=1);

namespace ElementorTwigKit\WordPress;

use ElementorTwigKit\Domain\Port\JobOfferSource;
use ElementorTwigKit\Infrastructure\Config\PluginConfig;
use ElementorTwigKit\Infrastructure\Logging\Logger;
use ElementorTwigKit\Infrastructure\Rendering\Renderer;
use RuntimeException;

/**
 * The composition root, exposed statically, and that is a deliberate trade-off.
 *
 * Elementor instantiates widget classes itself, with no arguments, on every
 * render, so their dependencies cannot come through the constructor. The two
 * ways out are a service locator or global functions; the locator at least names
 * its dependencies, can be replaced wholesale in a test, and confines the
 * constraint to this single class. Everything else in the plugin receives its
 * collaborators by injection.
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
            throw new RuntimeException('Services are not initialised: Plugin::boot() was never called.');
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
