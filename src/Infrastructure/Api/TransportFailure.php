<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Api;

use RuntimeException;

final class TransportFailure extends RuntimeException
{
    public static function network(string $url, string $reason): self
    {
        return new self(sprintf('Appel à « %s » impossible : %s.', $url, $reason));
    }

    public static function status(string $url, int $status): self
    {
        return new self(sprintf('Appel à « %s » rejeté avec le statut HTTP %d.', $url, $status));
    }

    public static function malformedPayload(string $reason): self
    {
        return new self(sprintf('Réponse illisible : %s.', $reason));
    }
}
