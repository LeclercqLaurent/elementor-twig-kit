<?php

declare(strict_types=1);

/**
 * Plugin Name: Elementor Twig Kit
 * Description: Preuve de concept — des widgets Elementor rendus par Twig, configurés par un fichier .env, testés hors-ligne.
 * Version: 0.1.0
 * Requires PHP: 8.2
 * Author: Codeam
 * License: MIT
 */

use ElementorTwigKit\WordPress\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';

// Sans « composer install », le plugin s'abstient au lieu de provoquer une
// erreur fatale sur toutes les pages du site.
if (!is_readable($autoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>Elementor Twig Kit : dépendances absentes, lancer « composer install ».</p></div>';
    });

    return;
}

require_once $autoload;

add_action('plugins_loaded', static function (): void {
    // Elementor est fourni par WordPress, pas par Composer : sa présence se
    // vérifie à l'exécution, une fois toutes les extensions chargées.
    if (!did_action('elementor/loaded')) {
        return;
    }

    (new Plugin(__DIR__))->boot();
});
