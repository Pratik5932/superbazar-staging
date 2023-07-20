<?php

declare (strict_types=1);
namespace AstraPrefixed\Http\Client\Common;

use AstraPrefixed\Psr\Http\Client\ClientInterface;
use AstraPrefixed\Psr\Http\Message\RequestInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
/**
 * Decorates an HTTP Client.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
trait HttpClientDecorator
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    /**
     * {@inheritdoc}
     *
     * @see ClientInterface::sendRequest
     */
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }
}
