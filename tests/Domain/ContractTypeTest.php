<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Domain;

use ElementorTwigKit\Domain\ContractType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContractTypeTest extends TestCase
{
    public function testEveryContractCarriesANonEmptyLabel(): void
    {
        foreach (ContractType::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }

    #[DataProvider('toleratedValues')]
    public function testReadingToleratesCaseAndDashes(string $raw, ContractType $expected): void
    {
        self::assertSame($expected, ContractType::tryFromLoose($raw));
    }

    /**
     * @return iterable<string, array{string, ContractType}>
     */
    public static function toleratedValues(): iterable
    {
        yield 'canonical value' => ['permanent', ContractType::Permanent];
        yield 'upper case' => ['PERMANENT', ContractType::Permanent];
        yield 'dashes' => ['fixed-term', ContractType::FixedTerm];
        yield 'stray spaces' => ['  Internship  ', ContractType::Internship];
    }

    public function testAnUnknownValueYieldsNull(): void
    {
        self::assertNull(ContractType::tryFromLoose('umbrella-company'));
    }
}
