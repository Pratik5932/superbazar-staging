<?php

declare (strict_types=1);
namespace AstraPrefixed\Http\Client\Curl;

use AstraPrefixed\Http\Message\Builder\ResponseBuilder as OriginalResponseBuilder;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
/**
 * Extended response builder.
 */
class ResponseBuilder extends OriginalResponseBuilder
{
    /**
     * Replace response with a new instance.
     *
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response) : void
    {
        $this->response = $response;
    }
}
