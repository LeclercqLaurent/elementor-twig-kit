<?php

declare(strict_types=1);

namespace ElementorTwigKit\Infrastructure\Config;

use RuntimeException;

final class MissingConfiguration extends RuntimeException
{
    /**
     * @param list<string> $keys
     */
    public static function keys(array $keys): self
    {
        return new self(sprintf(
            'Configuration incomplète : %s. Copier « .env.dist » en « .env » et renseigner ces clés.',
            implode(', ', $keys),
        ));
    }
}
