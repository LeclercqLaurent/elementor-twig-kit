<?php

declare(strict_types=1);

/**
 * Stubs Elementor — pour l'analyse statique et les tests uniquement.
 *
 * Elementor n'est pas une dépendance Composer : il est installé par WordPress.
 * Sans ces déclarations, PHPStan ne saurait rien des classes étendues par les
 * widgets et la CI devrait se contenter d'ignorer les erreurs. Ce fichier n'est
 * jamais chargé à l'exécution.
 */

namespace Elementor;

abstract class Widget_Base
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [], mixed $args = null)
    {
    }

    abstract public function get_name(): string;

    abstract public function get_title(): string;

    abstract public function get_icon(): string;

    /**
     * @return list<string>
     */
    abstract public function get_categories(): array;

    /**
     * @param array<string, mixed> $args
     */
    public function add_control(string $id, array $args): void
    {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function start_controls_section(string $id, array $args): void
    {
    }

    public function end_controls_section(): void
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function get_settings_for_display(?string $setting = null): array
    {
        return [];
    }
}

final class Controls_Manager
{
    public const TEXT = 'text';
    public const NUMBER = 'number';
    public const SELECT = 'select';
    public const TAB_CONTENT = 'content';
}

final class Widgets_Manager
{
    public function register(Widget_Base $widget): void
    {
    }
}
