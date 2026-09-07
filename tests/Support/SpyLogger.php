<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Support;

use ElementorTwigKit\Infrastructure\Logging\Logger;

final class SpyLogger implements Logger
{
    /** @var list<string> */
    public array $messages = [];

    public function warning(string $message): void
    {
        $this->messages[] = $message;
    }
}
