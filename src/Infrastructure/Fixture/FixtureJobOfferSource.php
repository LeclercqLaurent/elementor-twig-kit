<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Fixture;

use ElementorTwigKit\Domain\JobQuery;
use ElementorTwigKit\Domain\Port\JobOfferSource;
use ElementorTwigKit\Infrastructure\Api\JobOfferMapper;

/**
 * Source de démonstration : un jeu d'offres fictives embarqué dans le plugin.
 *
 * C'est ce qui rend cette preuve de concept exécutable telle quelle, sans API à
 * installer. C'est aussi, sur un vrai site, ce qui permet à un intégrateur de
 * composer ses pages Elementor avant que l'API ne soit prête. Le filtrage se
 * fait ici en mémoire, avec les mêmes critères que ceux envoyés à l'API
 * distante.
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
