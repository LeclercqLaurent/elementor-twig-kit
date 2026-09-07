<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain\Port;

use ElementorTwigKit\Domain\JobOffer;
use ElementorTwigKit\Domain\JobQuery;

/**
 * Port de sortie du domaine : « d'où viennent les offres » est une question
 * d'infrastructure. Le domaine et les widgets ne connaissent que ce contrat,
 * ce qui rend l'API distante et le jeu de démonstration interchangeables.
 */
interface JobOfferSource
{
    /**
     * @return list<JobOffer>
     */
    public function search(JobQuery $query): array;
}
