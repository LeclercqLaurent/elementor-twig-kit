<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Infrastructure\Api\CurlHttpClient;
use ElementorTwigKit\Infrastructure\Api\TransportFailure;
use PHPUnit\Framework\TestCase;

/**
 * Le seul adaptateur qui touche le réseau. Le test reste hors-ligne : il vise
 * un port fermé sur la boucle locale, ce qui échoue immédiatement et sans
 * résolution DNS — la suite ne dépend donc d'aucune connectivité.
 */
final class CurlHttpClientTest extends TestCase
{
    public function testUneConnexionRefuseeDevientUnePanneDeTransportNommee(): void
    {
        $this->expectException(TransportFailure::class);
        $this->expectExceptionMessage('http://127.0.0.1:1/offers');

        (new CurlHttpClient())->getJson('http://127.0.0.1:1/offers', ['Accept' => 'application/json'], 1);
    }

    public function testUneUrlNonExploitableNEchappePasDuTypeDException(): void
    {
        $this->expectException(TransportFailure::class);

        (new CurlHttpClient())->getJson('http://', [], 1);
    }
}
