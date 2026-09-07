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

    public function testACompleteOffer(): void
    {
        $offers = $this->mapper->mapAll(self::payload([self::item()]));

        self::assertCount(1, $offers);
        self::assertSame('DEMO-001', $offers[0]->reference);
        self::assertSame('Lyon (FR)', (string) $offers[0]->location);
        self::assertSame(ContractType::FixedTerm, $offers[0]->contract);
    }

    public function testAnUnknownContractFallsBackToTheDefaultOne(): void
    {
        $offers = $this->mapper->mapAll(self::payload([self::item(['contract' => 'umbrella'])]));

        self::assertSame(ContractType::Permanent, $offers[0]->contract);
    }

    public function testAnInvalidOfferIsSkippedAndLoggedWithoutLosingTheOthers(): void
    {
        $offers = $this->mapper->mapAll(self::payload([
            self::item(['url' => 'not-a-url']),
            self::item(['reference' => 'DEMO-002']),
        ]));

        self::assertCount(1, $offers);
        self::assertSame('DEMO-002', $offers[0]->reference);
        self::assertCount(1, $this->logger->messages);
        self::assertStringContainsString('Offer #0 skipped', $this->logger->messages[0]);
    }

    public function testABareListIsAcceptedJustLikeAnItemsEnvelope(): void
    {
        $offers = $this->mapper->mapAll((string) json_encode([self::item()]));

        self::assertCount(1, $offers);
    }

    public function testUnstructuredEntriesAreDiscarded(): void
    {
        $offers = $this->mapper->mapAll((string) json_encode(['text', 42, self::item()]));

        self::assertCount(1, $offers);
    }

    public function testUnreadableJsonIsATransportFailure(): void
    {
        $this->expectException(TransportFailure::class);

        $this->mapper->mapAll('{ this is not json');
    }

    public function testAPayloadThatIsNotAListIsATransportFailure(): void
    {
        $this->expectException(TransportFailure::class);
        $this->expectExceptionMessage('a list of offers is expected');

        $this->mapper->mapAll('{"items": "none"}');
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
            'title' => 'PHP developer',
            'company' => 'Fictional Software Works',
            'city' => 'Lyon',
            'country' => 'FR',
            'contract' => 'fixed-term',
            'excerpt' => 'A demonstration position.',
            'published_at' => '2026-09-01',
            'url' => 'https://example.invalid/jobs/demo-001',
        ], $overrides);
    }
}
