<?php

declare (strict_types=1);
namespace AstraPrefixed\Http\Adapter\Guzzle6;

use AstraPrefixed\GuzzleHttp\Client as GuzzleClient;
use AstraPrefixed\GuzzleHttp\ClientInterface;
use AstraPrefixed\GuzzleHttp\HandlerStack;
use AstraPrefixed\GuzzleHttp\Middleware;
use AstraPrefixed\Http\Client\HttpAsyncClient;
use AstraPrefixed\Http\Client\HttpClient;
use AstraPrefixed\Psr\Http\Message\RequestInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
/**
 * HTTP Adapter for Guzzle 6.
 *
 * @author David de Boer <david@ddeboer.nl>
 */
final class Client implements HttpClient, HttpAsyncClient
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * If you pass a Guzzle instance as $client, make sure to configure Guzzle to not
     * throw exceptions on HTTP error status codes, or this adapter will violate PSR-18.
     * See also self::buildClient at the bottom of this class.
     */
    public function __construct(?ClientInterface $client = null)
    {
        if (!$client) {
            $client = self::buildClient();
        }
        $this->client = $client;
    }
    /**
     * Factory method to create the Guzzle 6 adapter with custom Guzzle configuration.
     */
    public static function createWithConfig(array $config) : Client
    {
        return new self(self::buildClient($config));
    }
    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $promise = $this->sendAsyncRequest($request);
        return $promise->wait();
    }
    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        $promise = $this->client->sendAsync($request);
        return new Promise($promise, $request);
    }
    /**
     * Build the Guzzle client instance.
     */
    private static function buildClient(array $config = []) : GuzzleClient
    {
        $handlerStack = new HandlerStack(\AstraPrefixed\GuzzleHttp\choose_handler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = \array_merge(['handler' => $handlerStack], $config);
        return new GuzzleClient($config);
    }
}
