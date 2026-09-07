<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain\Port;

use ElementorTwigKit\Domain\JobOffer;
use ElementorTwigKit\Domain\JobQuery;

/**
 * The domain's outbound port: "where do the offers come from" is an
 * infrastructure question. The domain and the widgets know nothing but this
 * contract, which makes the remote API and the demo dataset interchangeable.
 */
interface JobOfferSource
{
    /**
     * @return list<JobOffer>
     */
    public function search(JobQuery $query): array;
}
