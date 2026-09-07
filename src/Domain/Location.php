<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain;

use ElementorTwigKit\Domain\Exception\InvalidJobOffer;

/**
 * Lieu d'un poste. Immuable et normalisé à la construction : le reste du code
 * n'a jamais à se demander si « lyon » et « Lyon » sont le même endroit.
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
            throw InvalidJobOffer::malformedField('location.country', 'code ISO 3166-1 alpha-2 attendu');
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
