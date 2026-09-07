<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Elementor n'est pas une dépendance Composer : les stubs suffisent aux tests
// qui touchent la couche WordPress sans installer un site complet.
require_once __DIR__ . '/../stubs/elementor.php';
