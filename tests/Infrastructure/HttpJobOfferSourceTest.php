<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Domain\JobQuery;
use ElementorTwigKit\Infrastructure\Api\HttpJobOfferSource;
use ElementorTwigKit\Infrastructure\Api\JobOfferMapper;
use ElementorTwigKit\Infrastructure\Config\Environment;
use ElementorTwigKit\Infrastructure\Config\PluginConfig;
use ElementorTwigKit\Tests\Support\InMemoryHttpClient;
use ElementorTwigKit\Tests\Support\SpyLogger;
use PHPUnit\Framework\TestCase;

final class HttpJobOfferSourceTest extends TestCase
{
    public function testLesCriteresPartentDansLUrlEtLeJetonDansLEntete(): void
    {
        $client = new InMemoryHttpClient('{"items": []}');

        $this->source($client)->search(new JobQuery(
            keywords: 'php',
            city: 'Lyon',
            contract: ContractType::Freelance,
            limit: 3,
        ));

        self::assertStringStartsWith('https://api.example.invalid/v1/offers?', $client->lastUrl);
        self::assertStringContainsString('q=php', $client->lastUrl);
        self::assertStringContainsString('city=Lyon', $client->lastUrl);
        self::assertStringContainsString('contract=freelance', $client->lastUrl);
        self::assertSame('Bearer jeton', $client->lastHeaders['Authorization']);
        self::assertSame('application/json', $client->lastHeaders['Accept']);
    }

    public function testLaReponseEstTraduiteEnObjetsDuDomaine(): void
    {
        $client = new InMemoryHttpClient((string) json_encode(['items' => [[
            'reference' => 'DEMO-009',
            'title' => 'Ingénieure plateforme',
            'company' => 'Coopérative Imaginaire',
            'city' => 'Genève',
            'country' => 'CH',
            'contract' => 'permanent',
            'excerpt' => 'Un poste de démonstration.',
            'published_at' => '2026-08-01',
            'url' => 'https://example.invalid/offres/demo-009',
        ]]]));

        $offers = $this->source($client)->search(new JobQuery());

        self::assertCount(1, $offers);
        self::assertSame('Genève (CH)', (string) $offers[0]->location);
    }

    private function source(InMemoryHttpClient $client): HttpJobOfferSource
    {
        $config = PluginConfig::fromEnvironment(Environment::fromArray([
            'JOBS_DEMO_MODE' => 'false',
            'JOBS_API_URL' => 'https://api.example.invalid/v1',
            'JOBS_API_TOKEN' => 'jeton',
        ]));

        return new HttpJobOfferSource($client, new JobOfferMapper(new SpyLogger()), $config);
    }
}
