<?php

namespace AstraPrefixed\Http\Discovery\Strategy;

use AstraPrefixed\Http\Client\HttpAsyncClient;
use AstraPrefixed\Http\Client\HttpClient;
use AstraPrefixed\Http\Mock\Client as Mock;
/**
 * Find the Mock client.
 *
 * @author Sam Rapaport <me@samrapdev.com>
 */
final class MockClientStrategy implements DiscoveryStrategy
{
    /**
     * {@inheritdoc}
     */
    public static function getCandidates($type)
    {
        if (\is_a(HttpClient::class, $type, \true) || \is_a(HttpAsyncClient::class, $type, \true)) {
            return [['class' => Mock::class, 'condition' => Mock::class]];
        }
        return [];
    }
}
