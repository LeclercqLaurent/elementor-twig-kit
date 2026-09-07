<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Api;

use ElementorTwigKit\Domain\JobQuery;
use ElementorTwigKit\Domain\Port\JobOfferSource;
use ElementorTwigKit\Infrastructure\Config\PluginConfig;

/**
 * Adaptateur de production du port « source d'offres » : une API REST distante.
 */
final readonly class HttpJobOfferSource implements JobOfferSource
{
    public function __construct(
        private HttpClient $client,
        private JobOfferMapper $mapper,
        private PluginConfig $config,
    ) {
    }

    public function search(JobQuery $query): array
    {
        $url = sprintf('%s/offers?%s', $this->config->apiUrl, http_build_query($query->toQueryParameters()));

        $json = $this->client->getJson(
            $url,
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->apiToken,
            ],
            $this->config->timeoutSeconds,
        );

        return $this->mapper->mapAll($json);
    }
}
