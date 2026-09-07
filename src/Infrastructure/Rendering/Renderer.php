<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Rendering;

interface Renderer
{
    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context): string;
}
