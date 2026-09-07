<?php

declare(strict_types=1);

namespace ElementorTwigKit\WordPress\Widget;

use Elementor\Widget_Base;
use ElementorTwigKit\WordPress\Services;

/**
 * The widget base: a widget declares its template and its context, never its
 * markup.
 *
 * Elementor's "render" method expects direct output, so it is kept down to an
 * "echo" of what Twig produced, which keeps everything testable (the building of
 * the context) genuinely testable.
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
     * Reads an Elementor setting, narrowed down to a safe string.
     */
    protected function setting(string $key, string $default = ''): string
    {
        /** @var mixed $value */
        $value = $this->get_settings_for_display()[$key] ?? null;

        return is_scalar($value) && '' !== (string) $value ? trim((string) $value) : $default;
    }

    /**
     * A stable, unique DOM id: two identical widgets dropped on the same page
     * must not produce the same "id" twice, or a label ends up bound to the
     * wrong field.
     */
    protected function domId(string $prefix): string
    {
        return $prefix . '-' . substr(md5(static::class . spl_object_hash($this)), 0, 8);
    }
}
