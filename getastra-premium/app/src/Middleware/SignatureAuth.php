<?php

namespace AstraPrefixed\GetAstra\Client\Middleware;

use AstraPrefixed\HttpSignatures\KeyStore;
use AstraPrefixed\HttpSignatures\Verifier;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Psr\SimpleCache\CacheInterface;
class SignatureAuth
{
    private $signatureVerifier;
    public function __construct(ContainerInterface $container)
    {
        /** @var CacheInterface $options */
        $options = $container['options'];
        $this->signatureVerifier = new Verifier(new KeyStore($options->get('serverKeys')));
    }
    /**
     * Request signature verification middleware.
     *
     * @param ServerRequestInterface $request  PSR7 request
     * @param ResponseInterface      $response PSR7 response
     * @param callable               $next     Next middleware
     *
     * @return ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if ($this->signatureVerifier->isSignedWithDigest($request)) {
            return $next($request->withAttribute('signature_verified', \true), $response);
        } else {
            return $next($request, $response->withStatus(401));
        }
    }
}
