<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Domain;

use ElementorTwigKit\Domain\ContractType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContractTypeTest extends TestCase
{
    public function testChaqueContratPorteUnLibelleNonVide(): void
    {
        foreach (ContractType::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }

    #[DataProvider('valeursTolerees')]
    public function testLaLectureTolereLaCasseEtLesTirets(string $raw, ContractType $expected): void
    {
        self::assertSame($expected, ContractType::tryFromLoose($raw));
    }

    /**
     * @return iterable<string, array{string, ContractType}>
     */
    public static function valeursTolerees(): iterable
    {
        yield 'valeur canonique' => ['permanent', ContractType::Permanent];
        yield 'majuscules' => ['PERMANENT', ContractType::Permanent];
        yield 'tirets' => ['fixed-term', ContractType::FixedTerm];
        yield 'espaces superflus' => ['  Internship  ', ContractType::Internship];
    }

    public function testUneValeurInconnueVautNull(): void
    {
        self::assertNull(ContractType::tryFromLoose('portage-salarial'));
    }
}
