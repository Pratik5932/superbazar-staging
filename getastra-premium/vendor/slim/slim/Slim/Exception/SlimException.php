<?php

/**
 * Slim Framework (https://slimframework.com)
 *
 * @license https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */
namespace AstraPrefixed\Slim\Exception;

use Exception;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
class SlimException extends Exception
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        parent::__construct();
        $this->request = $request;
        $this->response = $response;
    }
    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
