<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Api;

use CurlHandle;

final class CurlHttpClient implements HttpClient
{
    public function getJson(string $url, array $headers, int $timeoutSeconds): string
    {
        $handle = curl_init($url);

        if (!$handle instanceof CurlHandle) {
            throw TransportFailure::network($url, 'initialisation de cURL impossible');
        }

        try {
            return $this->execute($handle, $url, $headers, $timeoutSeconds);
        } finally {
            curl_close($handle);
        }
    }

    /**
     * @param array<string, string> $headers
     */
    private function execute(CurlHandle $handle, string $url, array $headers, int $timeoutSeconds): string
    {
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
        ]);

        $body = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

        if (!is_string($body)) {
            throw TransportFailure::network($url, curl_error($handle) ?: 'erreur inconnue');
        }

        if ($status < 200 || $status >= 300) {
            throw TransportFailure::status($url, $status);
        }

        return $body;
    }

    /**
     * @param array<string, string> $headers
     *
     * @return list<string>
     */
    private function formatHeaders(array $headers): array
    {
        $formatted = [];

        foreach ($headers as $name => $value) {
            $formatted[] = $name . ': ' . $value;
        }

        return $formatted;
    }
}
