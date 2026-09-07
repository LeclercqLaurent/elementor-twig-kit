<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Logging;

final class NullLogger implements Logger
{
    public function warning(string $message): void
    {
    }
}
