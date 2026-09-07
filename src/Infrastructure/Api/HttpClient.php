<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Api;

/**
 * The low-level HTTP transport port.
 *
 * It exists for one reason: to make the remote source testable without a
 * network. Tests inject an in-memory transport, production injects cURL, and the
 * code interpreting the response is the same in both cases.
 */
interface HttpClient
{
    /**
     * @param array<string, string> $headers
     *
     * @throws TransportFailure
     */
    public function getJson(string $url, array $headers, int $timeoutSeconds): string;
}
