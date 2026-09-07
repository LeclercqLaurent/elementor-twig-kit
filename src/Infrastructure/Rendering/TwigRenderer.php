<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Rendering;

use ElementorTwigKit\Infrastructure\Logging\Logger;
use Twig\Environment;
use Twig\Error\Error as TwigError;
use Twig\Loader\FilesystemLoader;

/**
 * Rendu des widgets par Twig plutôt que par des « echo » de HTML.
 *
 * C'est le cœur de la démonstration. Un widget Elementor rend traditionnellement
 * son balisage en concaténant des chaînes dans du PHP : l'échappement est
 * manuel, donc oublié tôt ou tard, et le balisage devient illisible dès qu'il y
 * a deux conditions. Twig échappe par défaut, sépare la structure de la logique,
 * et rend le gabarit surchargeable par le thème sans toucher au plugin.
 *
 * L'échec de rendu est traité comme une panne de bloc, pas de page : le widget
 * renvoie une chaîne vide et la cause part dans les journaux.
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
            $this->logger->warning(sprintf('Rendu de « %s » impossible : %s', $template, $error->getMessage()));

            return '';
        }
    }
}
