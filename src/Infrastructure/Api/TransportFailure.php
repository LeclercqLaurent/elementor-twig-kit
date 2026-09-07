<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Api;

use RuntimeException;

final class TransportFailure extends RuntimeException
{
    public static function network(string $url, string $reason): self
    {
        return new self(sprintf('Call to "%s" failed: %s.', $url, $reason));
    }

    public static function status(string $url, int $status): self
    {
        return new self(sprintf('Call to "%s" was rejected with HTTP status %d.', $url, $status));
    }

    public static function malformedPayload(string $reason): self
    {
        return new self(sprintf('Unreadable response: %s.', $reason));
    }
}
