<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Infrastructure\Api\JobOfferMapper;
use ElementorTwigKit\Infrastructure\Api\TransportFailure;
use ElementorTwigKit\Tests\Support\SpyLogger;
use PHPUnit\Framework\TestCase;

final class JobOfferMapperTest extends TestCase
{
    private SpyLogger $logger;
    private JobOfferMapper $mapper;

    protected function setUp(): void
    {
        $this->logger = new SpyLogger();
        $this->mapper = new JobOfferMapper($this->logger);
    }

    public function testUneOffreComplete(): void
    {
        $offers = $this->mapper->mapAll(self::payload([self::item()]));

        self::assertCount(1, $offers);
        self::assertSame('DEMO-001', $offers[0]->reference);
        self::assertSame('Lyon (FR)', (string) $offers[0]->location);
        self::assertSame(ContractType::FixedTerm, $offers[0]->contract);
    }

    public function testUnContratInconnuRetombeSurLeContratParDefaut(): void
    {
        $offers = $this->mapper->mapAll(self::payload([self::item(['contract' => 'portage'])]));

        self::assertSame(ContractType::Permanent, $offers[0]->contract);
    }

    public function testUneOffreInvalideEstIgnoreeEtJournaliseeSansPerdreLesAutres(): void
    {
        $offers = $this->mapper->mapAll(self::payload([
            self::item(['url' => 'pas-une-url']),
            self::item(['reference' => 'DEMO-002']),
        ]));

        self::assertCount(1, $offers);
        self::assertSame('DEMO-002', $offers[0]->reference);
        self::assertCount(1, $this->logger->messages);
        self::assertStringContainsString('Offre n°0 ignorée', $this->logger->messages[0]);
    }

    public function testUneListeNueEstAccepteeAuMemeTitreQuUneEnveloppeItems(): void
    {
        $offers = $this->mapper->mapAll((string) json_encode([self::item()]));

        self::assertCount(1, $offers);
    }

    public function testLesEntreesNonStructureesSontEcartees(): void
    {
        $offers = $this->mapper->mapAll((string) json_encode(['texte', 42, self::item()]));

        self::assertCount(1, $offers);
    }

    public function testUnJsonIllisibleEstUnePanneDeTransport(): void
    {
        $this->expectException(TransportFailure::class);

        $this->mapper->mapAll('{ ceci n\'est pas du json');
    }

    public function testUneChargeUtileQuiNEstPasUneListeEstUnePanneDeTransport(): void
    {
        $this->expectException(TransportFailure::class);
        $this->expectExceptionMessage('liste d\'offres attendue');

        $this->mapper->mapAll('{"items": "aucune"}');
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private static function payload(array $items): string
    {
        return (string) json_encode(['items' => $items]);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private static function item(array $overrides = []): array
    {
        return array_merge([
            'reference' => 'DEMO-001',
            'title' => 'Développeuse PHP',
            'company' => 'Fabrique Fictive',
            'city' => 'Lyon',
            'country' => 'FR',
            'contract' => 'fixed-term',
            'excerpt' => 'Un poste de démonstration.',
            'published_at' => '2026-09-01',
            'url' => 'https://example.invalid/offres/demo-001',
        ], $overrides);
    }
}
