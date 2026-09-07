<?php

declare(strict_types=1);

namespace ElementorTwigKit\WordPress\Widget;

use Elementor\Controls_Manager;
use ElementorTwigKit\Domain\ContractType;
use ElementorTwigKit\Domain\JobQuery;

/**
 * Formulaire de recherche, soumis en GET vers la page portant la liste.
 */
final class JobSearchWidget extends AbstractTwigWidget
{
    public function get_name(): string
    {
        return 'etk_job_search';
    }

    public function get_title(): string
    {
        return 'Job offers: search';
    }

    public function get_icon(): string
    {
        return 'eicon-search';
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => 'Content',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('action', [
            'label' => 'Results page',
            'type' => Controls_Manager::TEXT,
            'default' => '/jobs',
        ]);

        $this->end_controls_section();
    }

    protected function templateName(): string
    {
        return 'job-search.html.twig';
    }

    protected function templateContext(): array
    {
        /** @var array<string, mixed> $parameters */
        $parameters = $_GET;
        $query = JobQuery::fromRequest($parameters);

        return [
            'action' => $this->setting('action', '/jobs'),
            'id_prefix' => $this->domId('etk-search'),
            'contracts' => self::contractOptions(),
            'query' => [
                'keywords' => $query->keywords,
                'city' => $query->city,
                'contract' => null !== $query->contract ? $query->contract->value : '',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function contractOptions(): array
    {
        $options = [];

        foreach (ContractType::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
