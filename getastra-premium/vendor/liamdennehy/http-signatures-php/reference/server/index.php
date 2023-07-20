<?php

namespace AstraPrefixed;

require __DIR__ . '/../../vendor/autoload.php';
$referencePublicKey = ['Test' => \file_get_contents(__DIR__ . '/../keys/Test-public.pem')];
$verifierContext = new \AstraPrefixed\HttpSignatures\Context(['keys' => $referencePublicKey]);
$psr17Factory = new \AstraPrefixed\Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \AstraPrefixed\Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory,
    // ServerRequestFactory
    $psr17Factory,
    // UriFactory
    $psr17Factory,
    // UploadedFileFactory
    $psr17Factory
);
$body = [];
$serverRequest = $creator->fromGlobals();
if ($serverRequest->getHeader('Signature')) {
    $body['headers']['Signature'] = $serverRequest->getHeader('Signature')[0];
}
if ($serverRequest->getHeader('Authorization')) {
    $body['headers']['Authorization'] = $serverRequest->getHeader('Authorization')[0];
}
$body['signatures']['Authorization'] = $verifierContext->verifier()->isAuthorized($serverRequest);
$body['signatures']['Signature'] = $verifierContext->verifier()->isSigned($serverRequest);
$responseBody = $psr17Factory->createStream(\json_encode($body));
$response = $psr17Factory->createResponse(200)->withBody($responseBody);
(new \AstraPrefixed\Zend\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
