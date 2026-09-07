<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain;

use ElementorTwigKit\Domain\Exception\InvalidJobOffer;

/**
 * Where a job is based. Immutable and normalised on construction, so the rest of
 * the code never has to wonder whether "lyon" and "Lyon" are the same place.
 */
final readonly class Location
{
    public string $city;
    public string $country;

    public function __construct(string $city, string $country)
    {
        $city = trim($city);
        $country = strtoupper(trim($country));

        if ('' === $city) {
            throw InvalidJobOffer::emptyField('location.city');
        }

        if (1 !== preg_match('/^[A-Z]{2}$/', $country)) {
            throw InvalidJobOffer::malformedField('location.country', 'an ISO 3166-1 alpha-2 code is expected');
        }

        $this->city = $city;
        $this->country = $country;
    }

    public function matches(string $needle): bool
    {
        $needle = trim($needle);

        if ('' === $needle) {
            return true;
        }

        return str_contains(mb_strtolower($this->city), mb_strtolower($needle));
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->city, $this->country);
    }
}
