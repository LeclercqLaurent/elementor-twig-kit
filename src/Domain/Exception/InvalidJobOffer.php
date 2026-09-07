<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain\Exception;

use DomainException;

/**
 * Exception dedicated to the offers context: malformed incoming data never
 * surfaces as a generic language exception.
 */
final class InvalidJobOffer extends DomainException
{
    public static function emptyField(string $field): self
    {
        return new self(sprintf('Required field is empty: "%s".', $field));
    }

    public static function malformedField(string $field, string $expectation): self
    {
        return new self(sprintf('Field "%s" is malformed: %s.', $field, $expectation));
    }
}
