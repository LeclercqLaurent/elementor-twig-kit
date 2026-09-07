<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Api;

/**
 * Port bas niveau du transport HTTP.
 *
 * Il existe pour une seule raison : rendre la source distante testable sans
 * réseau. Les tests injectent un transport en mémoire, la production injecte
 * cURL, et le code qui interprète la réponse est le même dans les deux cas.
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
