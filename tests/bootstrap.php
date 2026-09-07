<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Elementor is not a Composer dependency: the stubs are enough for the tests
// that touch the WordPress layer, without installing a full site.
require_once __DIR__ . '/../stubs/elementor.php';
