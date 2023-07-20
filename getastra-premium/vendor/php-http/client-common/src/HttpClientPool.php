<?php

declare (strict_types=1);
namespace AstraPrefixed\Http\Client\Common;

use AstraPrefixed\Http\Client\Common\HttpClientPool\HttpClientPoolItem;
use AstraPrefixed\Http\Client\HttpAsyncClient;
use AstraPrefixed\Http\Client\HttpClient;
use AstraPrefixed\Psr\Http\Client\ClientInterface;
/**
 * A http client pool allows to send requests on a pool of different http client using a specific strategy (least used,
 * round robin, ...).
 */
interface HttpClientPool extends HttpAsyncClient, HttpClient
{
    /**
     * Add a client to the pool.
     *
     * @param ClientInterface|HttpAsyncClient|HttpClientPoolItem $client
     */
    public function addHttpClient($client) : void;
}
