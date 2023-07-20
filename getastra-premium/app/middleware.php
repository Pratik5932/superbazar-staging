<?php

namespace AstraPrefixed;

// Application middleware
// e.g: $app->add(new \Slim\Csrf\Guard);
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
$jws_settings = $astraContainer->get('settings')['jwt'];
$astraApp->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    //this header setting code doesn't work anyways
    return $response->withHeader('Access-Control-Allow-Origin', $this->get('settings')['cors'])->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
    //->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
//if (!isset($_GET['astraRoute'])) {
//    $astraApp->add(function (Request $request, Response $response, callable $next) {
//        $uri = $request->getUri();
//        $path = $uri->getPath();
//        if ('/' != $path && '/' == substr($path, -1)) {
//            // permanently redirect paths with a trailing slash
//            // to their non-trailing counterpart
//            $uri = $uri->withPath(substr($path, 0, -1));
//
//            $uriToRedirect = (string) $uri;
//            if ('GET' == $request->getMethod()) {
//                header('Location: '.$uriToRedirect, 301);
//                exit;
//            //return $response->withRedirect((string)$uri, 301);
//            } else {
//                return $next($request->withUri($uri), $response);
//            }
//        }
//
//        return $next($request, $response);
//    });
//}
