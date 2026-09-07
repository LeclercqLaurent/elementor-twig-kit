<?php

declare(strict_types=1);

/**
 * Plugin Name: Elementor Twig Kit
 * Description: Proof of concept: Elementor widgets rendered through Twig, configured by a .env file, tested offline.
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

// Without "composer install" the plugin stands down instead of raising a fatal
// error on every page of the site.
if (!is_readable($autoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>Elementor Twig Kit: dependencies are missing, run "composer install".</p></div>';
    });

    return;
}

require_once $autoload;

add_action('plugins_loaded', static function (): void {
    // Elementor is provided by WordPress, not by Composer: its presence is
    // checked at runtime, once every extension has loaded.
    if (!did_action('elementor/loaded')) {
        return;
    }

    (new Plugin(__DIR__))->boot();
});
