<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Logging;

/**
 * Writes to the server log, never to the page.
 *
 * An error message shown to a visitor informs an attacker and helps nobody else.
 * The prefix makes the plugin's lines findable in a WordPress log shared by
 * dozens of extensions.
 */
final class ErrorLogLogger implements Logger
{
    private const PREFIX = '[elementor-twig-kit] ';

    public function warning(string $message): void
    {
        error_log(self::PREFIX . $message);
    }
}
