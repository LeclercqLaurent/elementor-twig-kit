<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Rendering;

use ElementorTwigKit\Infrastructure\Logging\Logger;
use Twig\Environment;
use Twig\Error\Error as TwigError;
use Twig\Loader\FilesystemLoader;

/**
 * Rendering widgets through Twig instead of "echo"-ing HTML.
 *
 * This is the heart of the demonstration. An Elementor widget traditionally
 * renders its markup by concatenating strings in PHP: escaping is manual, hence
 * forgotten sooner or later, and the markup becomes unreadable as soon as there
 * are two conditions. Twig escapes by default, separates structure from logic,
 * and makes the template overridable by the theme without touching the plugin.
 *
 * A rendering failure is treated as a broken block, not a broken page: the
 * widget returns an empty string and the cause goes to the logs.
 */
final class TwigRenderer implements Renderer
{
    private readonly Environment $twig;

    public function __construct(
        string $templateDirectory,
        private readonly Logger $logger,
        bool $debug = false,
    ) {
        $this->twig = new Environment(new FilesystemLoader($templateDirectory), [
            'autoescape' => 'html',
            'strict_variables' => true,
            'debug' => $debug,
            'cache' => false,
        ]);
    }

    public function render(string $template, array $context): string
    {
        try {
            return $this->twig->render($template, $context);
        } catch (TwigError $error) {
            $this->logger->warning(sprintf('Could not render "%s": %s', $template, $error->getMessage()));

            return '';
        }
    }
}
