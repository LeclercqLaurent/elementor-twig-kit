<?php

declare(strict_types=1);

namespace ElementorTwigKit\WordPress\Widget;

use DateTimeImmutable;
use Elementor\Controls_Manager;
use ElementorTwigKit\Domain\JobQuery;
use ElementorTwigKit\WordPress\Services;

/**
 * Liste d'offres filtrée par les paramètres d'URL courants.
 */
final class JobListWidget extends AbstractTwigWidget
{
    private const ALLOWED_HEADING_LEVELS = ['h2', 'h3', 'h4'];

    public function get_name(): string
    {
        return 'etk_job_list';
    }

    public function get_title(): string
    {
        return 'Offres d\'emploi — liste';
    }

    public function get_icon(): string
    {
        return 'eicon-post-list';
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => 'Contenu',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('heading', [
            'label' => 'Titre de la section',
            'type' => Controls_Manager::TEXT,
            'default' => 'Nos offres',
        ]);

        $this->add_control('heading_level', [
            'label' => 'Niveau de titre',
            'type' => Controls_Manager::SELECT,
            'default' => 'h2',
            'options' => ['h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'],
        ]);

        $this->add_control('limit', [
            'label' => 'Nombre maximum d\'offres',
            'type' => Controls_Manager::NUMBER,
            'default' => JobQuery::DEFAULT_LIMIT,
        ]);

        $this->end_controls_section();
    }

    protected function templateName(): string
    {
        return 'job-list.html.twig';
    }

    protected function templateContext(): array
    {
        $services = Services::get();
        $now = new DateTimeImmutable();
        $query = $this->buildQuery();

        return [
            'heading' => $this->setting('heading', 'Nos offres'),
            'heading_level' => $this->headingLevel(),
            'heading_id' => $this->domId('etk-list'),
            'demo_mode' => $services->config->demoMode,
            'offers' => array_map(
                static fn ($offer): array => $offer->toTemplateContext($now),
                $services->offers->search($query),
            ),
        ];
    }

    private function buildQuery(): JobQuery
    {
        $request = JobQuery::fromRequest($this->requestParameters());
        $limit = (int) $this->setting('limit', (string) JobQuery::DEFAULT_LIMIT);

        return new JobQuery(
            keywords: $request->keywords,
            city: $request->city,
            contract: $request->contract,
            limit: $limit,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requestParameters(): array
    {
        /** @var array<string, mixed> $parameters */
        $parameters = $_GET;

        return $parameters;
    }

    private function headingLevel(): string
    {
        $level = strtolower($this->setting('heading_level', 'h2'));

        return in_array($level, self::ALLOWED_HEADING_LEVELS, true) ? $level : 'h2';
    }
}
