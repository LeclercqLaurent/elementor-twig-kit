<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Domain\JobQuery;
use ElementorTwigKit\Infrastructure\Api\JobOfferMapper;
use ElementorTwigKit\Infrastructure\Fixture\FixtureJobOfferSource;
use ElementorTwigKit\Tests\Support\SpyLogger;
use PHPUnit\Framework\TestCase;

final class FixtureJobOfferSourceTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/../../resources/fixtures/job-offers.json';

    public function testLeJeuEmbarqueEstIntegralementValide(): void
    {
        $logger = new SpyLogger();
        $offers = $this->source($logger, self::FIXTURE)->search(new JobQuery(limit: JobQuery::MAX_LIMIT));

        self::assertSame([], $logger->messages, 'aucune offre de démonstration ne doit être rejetée');
        self::assertGreaterThanOrEqual(5, count($offers));
    }

    public function testLeFiltrageEnMemoireSuitLesMemesCriteresQueLApi(): void
    {
        $source = $this->source(new SpyLogger(), self::FIXTURE);

        $lyonnaises = $source->search(new JobQuery(city: 'lyon', limit: JobQuery::MAX_LIMIT));
        self::assertNotEmpty($lyonnaises);

        foreach ($lyonnaises as $offer) {
            self::assertSame('Lyon', $offer->location->city);
        }

        $stages = $source->search(new JobQuery(contract: ContractType::Internship, limit: JobQuery::MAX_LIMIT));
        self::assertCount(1, $stages);
    }

    public function testLaLimiteEstAppliqueeApresLeFiltrage(): void
    {
        $offers = $this->source(new SpyLogger(), self::FIXTURE)->search(new JobQuery(limit: 2));

        self::assertCount(2, $offers);
    }

    public function testUnJeuDeDonneesAbsentDonneUneListeVideEtNonUneErreur(): void
    {
        $offers = $this->source(new SpyLogger(), __DIR__ . '/introuvable.json')->search(new JobQuery());

        self::assertSame([], $offers);
    }

    private function source(SpyLogger $logger, string $path): FixtureJobOfferSource
    {
        return new FixtureJobOfferSource(new JobOfferMapper($logger), $path);
    }
}
