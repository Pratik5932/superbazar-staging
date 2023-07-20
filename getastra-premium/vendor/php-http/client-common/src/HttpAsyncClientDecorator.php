<?php

declare (strict_types=1);
namespace AstraPrefixed\Http\Client\Common;

use AstraPrefixed\Http\Client\HttpAsyncClient;
use AstraPrefixed\Psr\Http\Message\RequestInterface;
/**
 * Decorates an HTTP Async Client.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
trait HttpAsyncClientDecorator
{
    /**
     * @var HttpAsyncClient
     */
    protected $httpAsyncClient;
    /**
     * {@inheritdoc}
     *
     * @see HttpAsyncClient::sendAsyncRequest
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        return $this->httpAsyncClient->sendAsyncRequest($request);
    }
}
