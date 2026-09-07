<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Fixture;

use ElementorTwigKit\Domain\JobQuery;
use ElementorTwigKit\Domain\Port\JobOfferSource;
use ElementorTwigKit\Infrastructure\Api\JobOfferMapper;

/**
 * The demo source: a set of fictional offers bundled with the plugin.
 *
 * It is what makes this proof of concept runnable as-is, with no API to install.
 * On a real site it is also what lets a builder lay out their Elementor pages
 * before the API is ready. Filtering happens in memory here, using the same
 * criteria that would be sent to the remote API.
 */
final readonly class FixtureJobOfferSource implements JobOfferSource
{
    public function __construct(
        private JobOfferMapper $mapper,
        private string $fixturePath,
    ) {
    }

    public function search(JobQuery $query): array
    {
        $json = is_readable($this->fixturePath) ? file_get_contents($this->fixturePath) : '[]';
        $offers = $this->mapper->mapAll(false === $json ? '[]' : $json);

        $matching = array_values(array_filter(
            $offers,
            static fn ($offer): bool => $query->matches($offer),
        ));

        return array_slice($matching, 0, $query->limit);
    }
}
