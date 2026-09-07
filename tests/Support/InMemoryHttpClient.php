<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Support;

use ElementorTwigKit\Infrastructure\Api\HttpClient;

final class InMemoryHttpClient implements HttpClient
{
    public string $lastUrl = '';

    /** @var array<string, string> */
    public array $lastHeaders = [];

    public function __construct(private readonly string $payload)
    {
    }

    public function getJson(string $url, array $headers, int $timeoutSeconds): string
    {
        $this->lastUrl = $url;
        $this->lastHeaders = $headers;

        return $this->payload;
    }
}
