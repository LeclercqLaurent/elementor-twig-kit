<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Domain;

use DateTimeImmutable;
use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Domain\JobOffer;
use ElementorTwigKit\Domain\JobQuery;
use ElementorTwigKit\Domain\Location;
use PHPUnit\Framework\TestCase;

final class JobQueryTest extends TestCase
{
    public function testLaLimiteEstBorneeDesLaConstruction(): void
    {
        self::assertSame(JobQuery::MAX_LIMIT, (new JobQuery(limit: 5000))->limit);
        self::assertSame(1, (new JobQuery(limit: -3))->limit);
        self::assertSame(12, (new JobQuery(limit: 12))->limit);
    }

    public function testUneRequeteHttpBricoleeNeCassePasLaRecherche(): void
    {
        $query = JobQuery::fromRequest([
            'q' => ['tableau', 'inattendu'],
            'city' => '  Lyon  ',
            'contract' => 'CONTRAT-INEXISTANT',
            'limit' => 'beaucoup',
        ]);

        self::assertSame('', $query->keywords);
        self::assertSame('Lyon', $query->city);
        self::assertNull($query->contract);
        self::assertSame(1, $query->limit);
    }

    public function testLesCriteresReconnusSontRetenus(): void
    {
        $query = JobQuery::fromRequest(['q' => 'php', 'contract' => 'Fixed-Term', 'limit' => '3']);

        self::assertSame('php', $query->keywords);
        self::assertSame(ContractType::FixedTerm, $query->contract);
        self::assertSame(3, $query->limit);
    }

    public function testUnCritereVideNeFiltreRien(): void
    {
        self::assertTrue((new JobQuery())->matches(self::offer()));
    }

    public function testLeFiltrageCombineContratVilleEtMotsCles(): void
    {
        $offer = self::offer();

        self::assertTrue((new JobQuery(keywords: 'FICTIVE'))->matches($offer));
        self::assertFalse((new JobQuery(keywords: 'kotlin'))->matches($offer));
        self::assertFalse((new JobQuery(city: 'Nantes'))->matches($offer));
        self::assertFalse((new JobQuery(contract: ContractType::Internship))->matches($offer));
    }

    public function testLesParametresDApiOmettentLesCriteresVides(): void
    {
        $parameters = (new JobQuery(keywords: 'php', contract: ContractType::Freelance, limit: 4))
            ->toQueryParameters();

        self::assertSame(['q' => 'php', 'contract' => 'freelance', 'limit' => '4'], $parameters);
    }

    private static function offer(): JobOffer
    {
        return new JobOffer(
            reference: 'DEMO-001',
            title: 'Développeuse PHP',
            company: 'Fabrique Fictive',
            location: new Location('Lyon', 'FR'),
            contract: ContractType::Permanent,
            excerpt: 'Un poste de démonstration.',
            publishedAt: new DateTimeImmutable('2026-09-01'),
            url: 'https://example.invalid/offres/demo-001',
        );
    }
}
