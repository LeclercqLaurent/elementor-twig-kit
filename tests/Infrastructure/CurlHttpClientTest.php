<?php

declare(strict_types=1);

namespace ElementorTwigKit\Tests\Infrastructure;

use ElementorTwigKit\Infrastructure\Api\CurlHttpClient;
use ElementorTwigKit\Infrastructure\Api\TransportFailure;
use PHPUnit\Framework\TestCase;

/**
 * The only adapter that touches the network. The test stays offline: it aims at
 * a closed port on the loopback interface, which fails immediately and with no
 * DNS resolution, so the suite depends on no connectivity at all.
 */
final class CurlHttpClientTest extends TestCase
{
    public function testARefusedConnectionBecomesANamedTransportFailure(): void
    {
        $this->expectException(TransportFailure::class);
        $this->expectExceptionMessage('http://127.0.0.1:1/offers');

        (new CurlHttpClient())->getJson('http://127.0.0.1:1/offers', ['Accept' => 'application/json'], 1);
    }

    public function testAnUnusableUrlDoesNotEscapeTheExceptionType(): void
    {
        $this->expectException(TransportFailure::class);

        (new CurlHttpClient())->getJson('http://', [], 1);
    }
}
