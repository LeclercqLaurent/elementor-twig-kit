<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use DateTimeImmutable;
use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Domain\JobOffer;
use ElementorTwigKit\Domain\Location;
use ElementorTwigKit\Infrastructure\Rendering\TwigRenderer;
use ElementorTwigKit\Tests\Support\SpyLogger;
use PHPUnit\Framework\TestCase;

final class TwigRendererTest extends TestCase
{
    private SpyLogger $logger;
    private TwigRenderer $renderer;

    protected function setUp(): void
    {
        $this->logger = new SpyLogger();
        $this->renderer = new TwigRenderer(__DIR__ . '/../../templates', $this->logger);
    }

    public function testTheListRendersOffersAndAnnouncesDemoMode(): void
    {
        $html = $this->renderer->render('job-list.html.twig', $this->listContext());

        self::assertStringContainsString('<h3 class="etk-list__heading" id="our-openings">Our openings</h3>', $html);
        self::assertStringContainsString('Demo dataset', $html);
        self::assertStringContainsString('PHP developer', $html);
        self::assertStringContainsString('aria-labelledby="our-openings"', $html);
    }

    public function testAnEmptyListShowsAMessageRatherThanAMuteSection(): void
    {
        $html = $this->renderer->render('job-list.html.twig', [
            ...$this->listContext(),
            'offers' => [],
        ]);

        self::assertStringContainsString('No offer matches this search', $html);
        self::assertStringNotContainsString('<ul', $html);
    }

    /**
     * The central point of using Twig here: escaping is not a discipline to keep
     * up line by line, it is the engine's default behaviour.
     */
    public function testContentComingFromTheApiIsEscapedWithNoDeveloperEffort(): void
    {
        $html = $this->renderer->render('job-list.html.twig', [
            ...$this->listContext(),
            'offers' => [$this->offerContext('<script>alert(1)</script>')],
        ]);

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testTheFormBindsEveryFieldToItsLabel(): void
    {
        $html = $this->renderer->render('job-search.html.twig', [
            'action' => '/jobs',
            'id_prefix' => 'etk-search-1234',
            'contracts' => ['permanent' => 'Permanent', 'internship' => 'Internship'],
            'query' => ['keywords' => 'php', 'city' => '', 'contract' => 'internship'],
        ]);

        foreach (['q', 'city', 'contract'] as $field) {
            self::assertStringContainsString(sprintf('for="etk-search-1234-%s"', $field), $html);
            self::assertStringContainsString(sprintf('id="etk-search-1234-%s"', $field), $html);
        }

        self::assertStringContainsString('<option value="internship" selected>Internship</option>', $html);
        self::assertStringContainsString('value="php"', $html);
    }

    public function testAMissingTemplateBlanksTheBlockAndLogsTheCause(): void
    {
        $html = $this->renderer->render('no-such-template.html.twig', []);

        self::assertSame('', $html);
        self::assertCount(1, $this->logger->messages);
        self::assertStringContainsString('no-such-template.html.twig', $this->logger->messages[0]);
    }

    /**
     * "strict_variables" turns a forgotten key into a failure visible in the
     * logs, rather than a silent hole in the page.
     */
    public function testAMissingVariableIsALoggedFailureAndNotASilentHole(): void
    {
        $html = $this->renderer->render('job-list.html.twig', ['heading' => 'Our openings']);

        self::assertSame('', $html);
        self::assertCount(1, $this->logger->messages);
    }

    /**
     * @return array<string, mixed>
     */
    private function listContext(): array
    {
        return [
            'heading' => 'Our openings',
            'heading_level' => 'h3',
            'heading_id' => 'our-openings',
            'demo_mode' => true,
            'offers' => [$this->offerContext()],
        ];
    }

    /**
     * @return array<string, string|bool>
     */
    private function offerContext(string $title = 'PHP developer'): array
    {
        return (new JobOffer(
            reference: 'DEMO-001',
            title: $title,
            company: 'Fictional Software Works',
            location: new Location('Lyon', 'FR'),
            contract: ContractType::Permanent,
            excerpt: 'A demonstration position.',
            publishedAt: new DateTimeImmutable('2026-09-01'),
            url: 'https://example.invalid/jobs/demo-001',
        ))->toTemplateContext(new DateTimeImmutable('2026-09-07'));
    }
}
