<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain;

/**
 * The kind of contract on offer.
 *
 * An enum rather than a string: the value coming from the API is validated once,
 * at mapping time, and never again, neither in the filters nor in the views.
 */
enum ContractType: string
{
    case Permanent = 'permanent';
    case FixedTerm = 'fixed_term';
    case Internship = 'internship';
    case Freelance = 'freelance';
    case Apprenticeship = 'apprenticeship';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent',
            self::FixedTerm => 'Fixed term',
            self::Internship => 'Internship',
            self::Freelance => 'Freelance',
            self::Apprenticeship => 'Apprenticeship',
        };
    }

    /**
     * Tolerates what an API actually returns: mixed case, dashes, stray spaces.
     * An unknown value yields null, and the mapper decides what to do with it.
     */
    public static function tryFromLoose(string $raw): ?self
    {
        $normalised = str_replace('-', '_', strtolower(trim($raw)));

        return self::tryFrom($normalised);
    }
}
