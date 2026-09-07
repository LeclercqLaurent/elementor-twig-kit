<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Logging;

/**
 * Écrit dans le journal du serveur, jamais dans la page.
 *
 * Un message d'erreur affiché à un visiteur renseigne un attaquant et n'aide
 * personne d'autre ; le préfixe permet de retrouver les lignes du plugin dans un
 * journal WordPress partagé par des dizaines d'extensions.
 */
final class ErrorLogLogger implements Logger
{
    private const PREFIX = '[elementor-twig-kit] ';

    public function warning(string $message): void
    {
        error_log(self::PREFIX . $message);
    }
}
