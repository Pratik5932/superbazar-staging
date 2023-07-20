<?php

/**
 * Created by PhpStorm.
 * User: anandakrishna
 * Date: 2019-03-09
 * Time: 18:36.
 */
namespace AstraPrefixed\GetAstra\Client\Middleware;

use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
class Responder
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var CommonHelper
     */
    private $commonHelper;
    /**
     * HaltResponse constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->commonHelper = new CommonHelper();
    }
    /**
     * HaltResponse middleware invokable class to halt the HTTP response when the X-HALT header is present in Response.
     *
     * @param ServerRequestInterface $request  PSR7 request
     * @param ResponseInterface      $response PSR7 response
     * @param callable               $next     Next middleware
     *
     * @return ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        //TODO: common flag to see if it is an astra api route
        $path = (string) $request->getUri()->getPath();
        $isApi = \false !== \strpos($path, 'api') && ASTRA_API_ROUTE == \true;
        if ($isApi) {
            $verifySignedRequest = $this->commonHelper->verifySignedHttpRequest($request, $this->container);
            if (!$verifySignedRequest['requestVerified']) {
                $this->respond($response->withJson(['error' => $verifySignedRequest['errorMessage']], 400));
                exit;
            }
            if (!$this->commonHelper->clientApiTokenCheck($request, $this->container)) {
                $this->respond($response->withJson(['error' => 'Unauthorized'], 400));
                exit;
            }
            if (!$this->commonHelper->IsIpAllowedToAccessPrivateApis($request, $this->container)) {
                $this->respond($response->withJson(['error' => 'Unauthorized'], 400));
                exit;
            }
        }
        /** @var Response $response */
        $response = $next($request, $response);
        $status = $response->getStatusCode();
        $shouldHalt = \in_array($status, ['200', '401', '201', '403']) || $response->hasHeader('X-Halt-Request');
        //$shouldHalt = $status !== "404" || $response->hasHeader('X-Halt-Request');
        if ($shouldHalt || $isApi) {
            $this->respond($response);
            exit;
        }
        return $response;
    }
    public function respond($response)
    {
        /* @var Response $response */
        // Send response
        if (!\headers_sent()) {
            // Headers
            foreach ($response->getHeaders() as $name => $values) {
                $first = 0 === \stripos($name, 'Set-Cookie') ? \false : \true;
                foreach ($values as $value) {
                    \header(\sprintf('%s: %s', $name, $value), $first);
                    $first = \false;
                }
            }
            // Set the status _after_ the headers, because of PHP's "helpful" behavior with location headers.
            // See https://github.com/slimphp/Slim/issues/1730
            // Status
            \header(\sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()), \true, $response->getStatusCode());
        }
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        $settings = $this->container->get('settings');
        $chunkSize = $settings['responseChunkSize'];
        $contentLength = $response->getHeaderLine('Content-Length');
        if (!$contentLength) {
            $contentLength = $body->getSize();
        }
        if (isset($contentLength)) {
            $amountToRead = $contentLength;
            while ($amountToRead > 0 && !$body->eof()) {
                $data = $body->read(\min((int) $chunkSize, (int) $amountToRead));
                echo $data;
                $amountToRead -= \strlen($data);
                if (\CONNECTION_NORMAL != \connection_status()) {
                    break;
                }
            }
        } else {
            while (!$body->eof()) {
                echo $body->read((int) $chunkSize);
                if (\CONNECTION_NORMAL != \connection_status()) {
                    break;
                }
            }
        }
    }
}
