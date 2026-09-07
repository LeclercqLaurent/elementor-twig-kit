<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Infrastructure\Config\Environment;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    private string $path = '';

    protected function setUp(): void
    {
        $directory = __DIR__ . '/../../var/test';

        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $this->path = $directory . '/' . uniqid('env-', true);
    }

    protected function tearDown(): void
    {
        if ('' !== $this->path && is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testUnFichierAbsentDonneUnEnvironnementVideEtNonUneErreur(): void
    {
        $environment = Environment::fromFile($this->path . '-inexistant');

        self::assertSame('repli', $environment->get('JOBS_API_URL', 'repli'));
    }

    public function testLesCommentairesLesLignesVidesEtLesGuillemetsSontIgnores(): void
    {
        file_put_contents($this->path, <<<'ENV'
            # un commentaire
            JOBS_API_URL="https://api.example.invalid"

            JOBS_API_TOKEN='secret'
            ligne sans signe egal
            JOBS_DEBUG = true
            ENV);

        $environment = Environment::fromFile($this->path);

        self::assertSame('https://api.example.invalid', $environment->get('JOBS_API_URL'));
        self::assertSame('secret', $environment->get('JOBS_API_TOKEN'));
        self::assertTrue($environment->bool('JOBS_DEBUG'));
    }

    public function testUneValeurVideRetombeSurLeDefaut(): void
    {
        $environment = Environment::fromArray(['JOBS_API_URL' => '']);

        self::assertSame('defaut', $environment->get('JOBS_API_URL', 'defaut'));
    }

    public function testLesBooleensAcceptentLesEcrituresCourantes(): void
    {
        $environment = Environment::fromArray([
            'A' => 'yes', 'B' => 'ON', 'C' => '1',
            'D' => 'no', 'E' => 'off', 'F' => '0',
            'G' => 'peut-etre',
        ]);

        foreach (['A', 'B', 'C'] as $key) {
            self::assertTrue($environment->bool($key), $key);
        }

        foreach (['D', 'E', 'F'] as $key) {
            self::assertFalse($environment->bool($key, true), $key);
        }

        self::assertTrue($environment->bool('G', true));
        self::assertFalse($environment->bool('ABSENTE'));
    }

    public function testLesNombresDecimauxRetombentSurLeDefautSiNonNumeriques(): void
    {
        $environment = Environment::fromArray(['SEUIL' => '0.7', 'CASSE' => 'beaucoup']);

        self::assertSame(0.7, $environment->float('SEUIL', 0.5));
        self::assertSame(0.5, $environment->float('CASSE', 0.5));
    }
}
