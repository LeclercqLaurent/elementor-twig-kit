<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain;

/**
 * Search criteria, built from the HTTP request and passed as-is to the source.
 * Grouping these primitives into one object avoids threading four positional
 * parameters through the whole call chain.
 */
final readonly class JobQuery
{
    public const DEFAULT_LIMIT = 10;
    public const MAX_LIMIT = 50;

    public int $limit;

    public function __construct(
        public string $keywords = '',
        public string $city = '',
        public ?ContractType $contract = null,
        int $limit = self::DEFAULT_LIMIT,
    ) {
        $this->limit = max(1, min($limit, self::MAX_LIMIT));
    }

    /**
     * Builds the criteria from untrusted parameters (typically $_GET): anything
     * unrecognised is ignored rather than rejected, because a hand-edited URL
     * must not break the page.
     *
     * @param array<string, mixed> $parameters
     */
    public static function fromRequest(array $parameters): self
    {
        $contract = is_string($parameters['contract'] ?? null)
            ? ContractType::tryFromLoose($parameters['contract'])
            : null;

        return new self(
            keywords: self::readString($parameters, 'q'),
            city: self::readString($parameters, 'city'),
            contract: $contract,
            limit: (int) (self::readString($parameters, 'limit') ?: self::DEFAULT_LIMIT),
        );
    }

    public function matches(JobOffer $offer): bool
    {
        if (null !== $this->contract && $offer->contract !== $this->contract) {
            return false;
        }

        if (!$offer->location->matches($this->city)) {
            return false;
        }

        return $this->matchesKeywords($offer);
    }

    /**
     * @return array<string, string>
     */
    public function toQueryParameters(): array
    {
        return array_filter([
            'q' => $this->keywords,
            'city' => $this->city,
            'contract' => null !== $this->contract ? $this->contract->value : '',
            'limit' => (string) $this->limit,
        ], static fn (string $value): bool => '' !== $value);
    }

    private function matchesKeywords(JobOffer $offer): bool
    {
        if ('' === $this->keywords) {
            return true;
        }

        $haystack = mb_strtolower($offer->title . ' ' . $offer->company . ' ' . $offer->excerpt);

        return str_contains($haystack, mb_strtolower($this->keywords));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private static function readString(array $parameters, string $key): string
    {
        $value = $parameters[$key] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }
}
