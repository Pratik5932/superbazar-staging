<?php

namespace AstraPrefixed\GetAstra\Client\Exception;

use Exception;
use AstraPrefixed\Illuminate\Database\Eloquent\ModelNotFoundException;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Slim\Handlers\Error;
use AstraPrefixed\Slim\Handlers\NotFound;
class ErrorHandler extends Error
{
    /** {@inheritdoc} */
    public function __construct(bool $displayErrorDetails)
    {
        parent::__construct($displayErrorDetails);
    }
    /** {@inheritdoc} */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Exception $exception)
    {
        /*
                if ($exception instanceof ModelNotFoundException) {
           return (new NotFound()($request, $response);
                }
                *
        */
        return parent::__invoke($request, $response, $exception);
    }
}
