<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Logging;

interface Logger
{
    public function warning(string $message): void;
}
