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
    public function testUneOffreSansReferenceEstImpossibleAConstruire(): void
    {
        $this->expectException(InvalidJobOffer::class);

        self::offer(reference: '  ');
    }

    public function testUneOffreSansTitreEstImpossibleAConstruire(): void
    {
        $this->expectException(InvalidJobOffer::class);

        self::offer(title: '');
    }

    public function testUneUrlRelativeEstRefusee(): void
    {
        $this->expectException(InvalidJobOffer::class);

        self::offer(url: '/offres/demo');
    }

    public function testLaFraicheurSeMesureParRapportAUneDateFournie(): void
    {
        $offer = self::offer(publishedAt: '2026-09-01');

        self::assertTrue($offer->isRecent(new DateTimeImmutable('2026-09-07')));
        self::assertFalse($offer->isRecent(new DateTimeImmutable('2026-10-07')));
    }

    public function testLeContexteDeGabaritNExposeQueDesValeursPresentables(): void
    {
        $context = self::offer()->toTemplateContext(new DateTimeImmutable('2026-09-07'));

        self::assertSame('Lyon (FR)', $context['location']);
        self::assertSame('CDI', $context['contract']);
        self::assertSame('01/09/2026', $context['published_at']);
        self::assertTrue($context['is_recent']);

        foreach ($context as $key => $value) {
            self::assertTrue(is_string($value) || is_bool($value), sprintf('« %s » n\'est pas scalaire', $key));
        }
    }

    private static function offer(
        string $reference = 'DEMO-001',
        string $title = 'Développeuse PHP',
        string $url = 'https://example.invalid/offres/demo-001',
        string $publishedAt = '2026-09-01',
    ): JobOffer {
        return new JobOffer(
            reference: $reference,
            title: $title,
            company: 'Fabrique Fictive',
            location: new Location('Lyon', 'FR'),
            contract: ContractType::Permanent,
            excerpt: 'Un poste de démonstration.',
            publishedAt: new DateTimeImmutable($publishedAt),
            url: $url,
        );
    }
}
