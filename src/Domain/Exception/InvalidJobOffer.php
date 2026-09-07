<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain\Exception;

use DomainException;

/**
 * Exception dédiée au contexte « offres » : une donnée entrante non conforme
 * ne remonte jamais sous la forme d'une exception générique du langage.
 */
final class InvalidJobOffer extends DomainException
{
    public static function emptyField(string $field): self
    {
        return new self(sprintf('Champ obligatoire vide : « %s ».', $field));
    }

    public static function malformedField(string $field, string $expectation): self
    {
        return new self(sprintf('Champ « %s » mal formé : %s.', $field, $expectation));
    }
}
