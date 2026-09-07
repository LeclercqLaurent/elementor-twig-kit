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

    public function testLaListeRendLesOffresEtAnnonceLeModeDemonstration(): void
    {
        $html = $this->renderer->render('job-list.html.twig', $this->listContext());

        self::assertStringContainsString('<h3 class="etk-list__heading" id="nos-offres">Nos offres</h3>', $html);
        self::assertStringContainsString('Jeu de démonstration', $html);
        self::assertStringContainsString('Développeuse PHP', $html);
        self::assertStringContainsString('aria-labelledby="nos-offres"', $html);
    }

    public function testUneListeVideAfficheUnMessageEtNonUneSectionMuette(): void
    {
        $html = $this->renderer->render('job-list.html.twig', [
            ...$this->listContext(),
            'offers' => [],
        ]);

        self::assertStringContainsString('Aucune offre ne correspond', $html);
        self::assertStringNotContainsString('<ul', $html);
    }

    /**
     * L'intérêt central de Twig ici : l'échappement n'est pas une discipline à
     * tenir ligne à ligne, c'est le comportement par défaut du moteur.
     */
    public function testLeContenuVenuDeLApiEstEchappeSansGesteDuDeveloppeur(): void
    {
        $html = $this->renderer->render('job-list.html.twig', [
            ...$this->listContext(),
            'offers' => [$this->offerContext('<script>alert(1)</script>')],
        ]);

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testLeFormulaireAssocieChaqueChampASonLabel(): void
    {
        $html = $this->renderer->render('job-search.html.twig', [
            'action' => '/offres',
            'id_prefix' => 'etk-search-1234',
            'contracts' => ['permanent' => 'CDI', 'internship' => 'Stage'],
            'query' => ['keywords' => 'php', 'city' => '', 'contract' => 'internship'],
        ]);

        foreach (['q', 'city', 'contract'] as $field) {
            self::assertStringContainsString(sprintf('for="etk-search-1234-%s"', $field), $html);
            self::assertStringContainsString(sprintf('id="etk-search-1234-%s"', $field), $html);
        }

        self::assertStringContainsString('<option value="internship" selected>Stage</option>', $html);
        self::assertStringContainsString('value="php"', $html);
    }

    public function testUnGabaritIntrouvableEteintLeBlocEtJournaliseLaCause(): void
    {
        $html = $this->renderer->render('inexistant.html.twig', []);

        self::assertSame('', $html);
        self::assertCount(1, $this->logger->messages);
        self::assertStringContainsString('inexistant.html.twig', $this->logger->messages[0]);
    }

    /**
     * « strict_variables » transforme une clé oubliée en panne visible dans les
     * journaux plutôt qu'en trou silencieux dans la page.
     */
    public function testUneVariableManquanteEstUnePanneJournaliseeEtNonUnTrouSilencieux(): void
    {
        $html = $this->renderer->render('job-list.html.twig', ['heading' => 'Nos offres']);

        self::assertSame('', $html);
        self::assertCount(1, $this->logger->messages);
    }

    /**
     * @return array<string, mixed>
     */
    private function listContext(): array
    {
        return [
            'heading' => 'Nos offres',
            'heading_level' => 'h3',
            'heading_id' => 'nos-offres',
            'demo_mode' => true,
            'offers' => [$this->offerContext()],
        ];
    }

    /**
     * @return array<string, string|bool>
     */
    private function offerContext(string $title = 'Développeuse PHP'): array
    {
        return (new JobOffer(
            reference: 'DEMO-001',
            title: $title,
            company: 'Fabrique Fictive',
            location: new Location('Lyon', 'FR'),
            contract: ContractType::Permanent,
            excerpt: 'Un poste de démonstration.',
            publishedAt: new DateTimeImmutable('2026-09-01'),
            url: 'https://example.invalid/offres/demo-001',
        ))->toTemplateContext(new DateTimeImmutable('2026-09-07'));
    }
}
