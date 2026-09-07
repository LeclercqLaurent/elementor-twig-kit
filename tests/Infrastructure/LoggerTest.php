<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Infrastructure\Logging\ErrorLogLogger;
use ElementorTwigKit\Infrastructure\Logging\NullLogger;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    public function testLeJournalDuServeurRecoitUneLignePrefixee(): void
    {
        $directory = __DIR__ . '/../../var/test';

        if (!is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $file = $directory . '/error.log';
        $previous = (string) ini_get('error_log');

        ini_set('error_log', $file);

        try {
            (new ErrorLogLogger())->warning('API injoignable');
        } finally {
            ini_set('error_log', $previous);
        }

        self::assertStringContainsString('[elementor-twig-kit] API injoignable', (string) file_get_contents($file));

        unlink($file);
    }

    public function testLeJournalNeutreNeFaitRienEtNeLevePasDErreur(): void
    {
        $this->expectNotToPerformAssertions();

        (new NullLogger())->warning('ignoré');
    }
}
