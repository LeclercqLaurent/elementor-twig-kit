<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Domain;

use DateTimeImmutable;
use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Domain\Exception\InvalidJobOffer;
use ElementorTwigKit\Domain\JobOffer;
use ElementorTwigKit\Domain\Location;
use PHPUnit\Framework\TestCase;

final class JobOfferTest extends TestCase
{
    public function testAnOfferWithoutAReferenceCannotBeBuilt(): void
    {
        $this->expectException(InvalidJobOffer::class);

        self::offer(reference: '  ');
    }

    public function testAnOfferWithoutATitleCannotBeBuilt(): void
    {
        $this->expectException(InvalidJobOffer::class);

        self::offer(title: '');
    }

    public function testARelativeUrlIsRejected(): void
    {
        $this->expectException(InvalidJobOffer::class);

        self::offer(url: '/jobs/demo');
    }

    public function testFreshnessIsMeasuredAgainstAGivenDate(): void
    {
        $offer = self::offer(publishedAt: '2026-09-01');

        self::assertTrue($offer->isRecent(new DateTimeImmutable('2026-09-07')));
        self::assertFalse($offer->isRecent(new DateTimeImmutable('2026-10-07')));
    }

    public function testTheTemplateContextOnlyExposesPresentableValues(): void
    {
        $context = self::offer()->toTemplateContext(new DateTimeImmutable('2026-09-07'));

        self::assertSame('Lyon (FR)', $context['location']);
        self::assertSame('Permanent', $context['contract']);
        self::assertSame('01/09/2026', $context['published_at']);
        self::assertTrue($context['is_recent']);

        foreach ($context as $key => $value) {
            self::assertTrue(is_string($value) || is_bool($value), sprintf('"%s" is not a scalar', $key));
        }
    }

    private static function offer(
        string $reference = 'DEMO-001',
        string $title = 'PHP developer',
        string $url = 'https://example.invalid/jobs/demo-001',
        string $publishedAt = '2026-09-01',
    ): JobOffer {
        return new JobOffer(
            reference: $reference,
            title: $title,
            company: 'Fictional Software Works',
            location: new Location('Lyon', 'FR'),
            contract: ContractType::Permanent,
            excerpt: 'A demonstration position.',
            publishedAt: new DateTimeImmutable($publishedAt),
            url: $url,
        );
    }
}
