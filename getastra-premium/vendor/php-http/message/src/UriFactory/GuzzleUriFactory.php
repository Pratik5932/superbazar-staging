<?php

namespace AstraPrefixed\Http\Message\UriFactory;

use function AstraPrefixed\GuzzleHttp\Psr7\uri_for;
use AstraPrefixed\GuzzleHttp\Psr7\Utils;
use AstraPrefixed\Http\Message\UriFactory;
/**
 * Creates Guzzle URI.
 *
 * @author David de Boer <david@ddeboer.nl>
 *
 * @deprecated This will be removed in php-http/message2.0. Consider using the official Guzzle PSR-17 factory
 */
final class GuzzleUriFactory implements UriFactory
{
    /**
     * {@inheritdoc}
     */
    public function createUri($uri)
    {
        if (\class_exists(Utils::class)) {
            return Utils::uriFor($uri);
        }
        return uri_for($uri);
    }
}