<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Domain;

use ElementorTwigKit\Domain\Exception\InvalidJobOffer;
use ElementorTwigKit\Domain\Location;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase
{
    public function testLePaysEstNormaliseEnMajuscules(): void
    {
        $location = new Location(' Lyon ', 'fr');

        self::assertSame('Lyon', $location->city);
        self::assertSame('FR', $location->country);
        self::assertSame('Lyon (FR)', (string) $location);
    }

    public function testUneVilleVideEstRefusee(): void
    {
        $this->expectException(InvalidJobOffer::class);

        new Location('   ', 'FR');
    }

    public function testUnPaysHorsNormeIsoEstRefuse(): void
    {
        $this->expectException(InvalidJobOffer::class);

        new Location('Lyon', 'France');
    }

    public function testLaComparaisonIgnoreLaCasseEtAccepteUnCritereVide(): void
    {
        $location = new Location('Villeurbanne', 'FR');

        self::assertTrue($location->matches(''));
        self::assertTrue($location->matches('villeur'));
        self::assertFalse($location->matches('Nantes'));
    }
}
