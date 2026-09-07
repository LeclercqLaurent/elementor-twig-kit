<?php

declare(strict_types=1);

namespace ElementorTwigKit\Domain;

/**
 * Nature du contrat proposé.
 *
 * Un enum plutôt qu'une chaîne : la valeur venue de l'API est validée une fois,
 * au mapping, et plus jamais ensuite — ni dans les filtres, ni dans les vues.
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
            self::Permanent => 'CDI',
            self::FixedTerm => 'CDD',
            self::Internship => 'Stage',
            self::Freelance => 'Freelance',
            self::Apprenticeship => 'Alternance',
        };
    }

    /**
     * Tolère ce qu'une API renvoie réellement : casse variable, tirets, espaces.
     * Une valeur inconnue vaut null — au mapping de décider quoi en faire.
     */
    public static function tryFromLoose(string $raw): ?self
    {
        $normalised = str_replace('-', '_', strtolower(trim($raw)));

        return self::tryFrom($normalised);
    }
}
