<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim-Psr7/blob/master/LICENSE.md (MIT License)
 */
declare (strict_types=1);
namespace AstraPrefixed\Slim\Psr7\Factory;

use InvalidArgumentException;
use AstraPrefixed\Psr\Http\Message\RequestFactoryInterface;
use AstraPrefixed\Psr\Http\Message\RequestInterface;
use AstraPrefixed\Psr\Http\Message\StreamFactoryInterface;
use AstraPrefixed\Psr\Http\Message\UriFactoryInterface;
use AstraPrefixed\Psr\Http\Message\UriInterface;
use AstraPrefixed\Slim\Psr7\Headers;
use AstraPrefixed\Slim\Psr7\Request;
class RequestFactory implements RequestFactoryInterface
{
    /**
     * @var StreamFactoryInterface|StreamFactory
     */
    protected $streamFactory;
    /**
     * @var UriFactoryInterface|UriFactory
     */
    protected $uriFactory;
    /**
     * @param StreamFactoryInterface|null $streamFactory
     * @param UriFactoryInterface|null    $uriFactory
     */
    public function __construct(?StreamFactoryInterface $streamFactory = null, ?UriFactoryInterface $uriFactory = null)
    {
        if (!isset($streamFactory)) {
            $streamFactory = new StreamFactory();
        }
        if (!isset($uriFactory)) {
            $uriFactory = new UriFactory();
        }
        $this->streamFactory = $streamFactory;
        $this->uriFactory = $uriFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri) : RequestInterface
    {
        if (\is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }
        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException('Parameter 2 of RequestFactory::createRequest() must be a string or a compatible UriInterface.');
        }
        $body = $this->streamFactory->createStream();
        return new Request($method, $uri, new Headers(), [], [], $body);
    }
}
