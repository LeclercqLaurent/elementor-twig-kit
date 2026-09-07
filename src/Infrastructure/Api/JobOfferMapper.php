<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Api;

use DateTimeImmutable;
use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Domain\JobOffer;
use ElementorTwigKit\Domain\Location;
use ElementorTwigKit\Infrastructure\Logging\Logger;
use Exception;
use Throwable;

/**
 * Translates the API payload into domain objects.
 *
 * Deliberate stance: an invalid entry is **skipped and logged**, never fatal. A
 * single badly filled offer in the back office must not empty the results page
 * of the public site.
 */
final readonly class JobOfferMapper
{
    public function __construct(private Logger $logger)
    {
    }

    /**
     * @return list<JobOffer>
     */
    public function mapAll(string $json): array
    {
        $offers = [];

        foreach ($this->decodeItems($json) as $index => $item) {
            $offer = $this->mapOne($item, $index);

            if ($offer instanceof JobOffer) {
                $offers[] = $offer;
            }
        }

        return $offers;
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function decodeItems(string $json): array
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw TransportFailure::malformedPayload($exception->getMessage());
        }

        $items = is_array($decoded) && isset($decoded['items']) ? $decoded['items'] : $decoded;

        if (!is_array($items)) {
            throw TransportFailure::malformedPayload('a list of offers is expected');
        }

        return array_values(array_filter($items, 'is_array'));
    }

    /**
     * @param array<array-key, mixed> $item
     */
    private function mapOne(array $item, int $index): ?JobOffer
    {
        try {
            return new JobOffer(
                reference: $this->string($item, 'reference'),
                title: $this->string($item, 'title'),
                company: $this->string($item, 'company'),
                location: new Location($this->string($item, 'city'), $this->string($item, 'country')),
                contract: ContractType::tryFromLoose($this->string($item, 'contract')) ?? ContractType::Permanent,
                excerpt: $this->string($item, 'excerpt'),
                publishedAt: new DateTimeImmutable($this->string($item, 'published_at') ?: 'now'),
                url: $this->string($item, 'url'),
            );
        } catch (Exception $exception) {
            $this->logger->warning(sprintf('Offer #%d skipped: %s', $index, $exception->getMessage()));

            return null;
        }
    }

    /**
     * @param array<array-key, mixed> $item
     */
    private function string(array $item, string $key): string
    {
        $value = $item[$key] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }
}
