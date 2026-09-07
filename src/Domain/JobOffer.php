<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain;

use DateTimeImmutable;
use ElementorTwigKit\Domain\Exception\InvalidJobOffer;

/**
 * Une offre d'emploi, telle que le rendu la manipule.
 *
 * Immuable et valide par construction : impossible d'obtenir une instance dont
 * la référence serait vide ou la date de publication absente. Les gabarits Twig
 * peuvent donc afficher ses champs sans les tester un par un.
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
            throw InvalidJobOffer::malformedField('url', 'URL absolue attendue');
        }
    }

    public function isRecent(DateTimeImmutable $now, int $days = 14): bool
    {
        return $this->publishedAt >= $now->modify(sprintf('-%d days', $days));
    }

    /**
     * Vue aplatie destinée aux gabarits : Twig reçoit des scalaires déjà
     * présentables, jamais la charge utile brute de l'API.
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
