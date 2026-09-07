<?php

declare(strict_types=1);

namespace ElementorTwigKit\WordPress\Widget;

use Elementor\Widget_Base;
use ElementorTwigKit\WordPress\Services;

/**
 * Socle des widgets : un widget déclare son gabarit et son contexte, jamais son
 * balisage.
 *
 * La méthode « render » d'Elementor attend un affichage direct ; on la garde
 * réduite à un « echo » de ce que Twig a produit, pour que tout ce qui est
 * testable (la construction du contexte) le reste vraiment.
 */
abstract class AbstractTwigWidget extends Widget_Base
{
    public function get_categories(): array
    {
        return ['general'];
    }

    protected function render(): void
    {
        $services = Services::get();

        echo $services->renderer->render($this->templateName(), $this->templateContext());
    }

    abstract protected function templateName(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function templateContext(): array;

    /**
     * Lecture d'un réglage Elementor, ramenée à une chaîne sûre.
     */
    protected function setting(string $key, string $default = ''): string
    {
        /** @var mixed $value */
        $value = $this->get_settings_for_display()[$key] ?? null;

        return is_scalar($value) && '' !== (string) $value ? trim((string) $value) : $default;
    }

    /**
     * Identifiant DOM stable et unique : deux widgets identiques posés sur la
     * même page ne doivent pas produire deux fois le même « id », sous peine
     * d'associer un label au mauvais champ.
     */
    protected function domId(string $prefix): string
    {
        return $prefix . '-' . substr(md5(static::class . spl_object_hash($this)), 0, 8);
    }
}
