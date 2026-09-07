<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain;

use DateTimeImmutable;
use ElementorTwigKit\Domain\Exception\InvalidJobOffer;

/**
 * A job offer, as the rendering layer handles it.
 *
 * Immutable and valid by construction: there is no way to obtain an instance
 * with an empty reference or a missing publication date. Twig templates can
 * therefore print its fields without testing them one by one.
 */
final readonly class JobOffer
{
    public function __construct(
        public string $reference,
        public string $title,
        public string $company,
        public Location $location,
        public ContractType $contract,
        public string $excerpt,
        public DateTimeImmutable $publishedAt,
        public string $url,
    ) {
        if ('' === trim($reference)) {
            throw InvalidJobOffer::emptyField('reference');
        }

        if ('' === trim($title)) {
            throw InvalidJobOffer::emptyField('title');
        }

        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw InvalidJobOffer::malformedField('url', 'an absolute URL is expected');
        }
    }

    public function isRecent(DateTimeImmutable $now, int $days = 14): bool
    {
        return $this->publishedAt >= $now->modify(sprintf('-%d days', $days));
    }

    /**
     * A flattened view for the templates: Twig receives scalars that are already
     * presentable, never the raw API payload.
     *
     * @return array<string, string|bool>
     */
    public function toTemplateContext(DateTimeImmutable $now): array
    {
        return [
            'reference' => $this->reference,
            'title' => $this->title,
            'company' => $this->company,
            'location' => (string) $this->location,
            'contract' => $this->contract->label(),
            'excerpt' => $this->excerpt,
            'published_at' => $this->publishedAt->format('d/m/Y'),
            'url' => $this->url,
            'is_recent' => $this->isRecent($now),
        ];
    }
}
