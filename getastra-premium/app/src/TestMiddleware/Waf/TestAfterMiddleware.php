<?php

namespace AstraPrefixed;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//namespace GetAstra\Client\Plugin\Waf;
//
//use Psr\Http\Message\ResponseInterface;
//use Psr\Http\Message\ServerRequestInterface;
//
///**
// * Description of TestAfterMiddleware.
// *
// * @author aditya
// */
//class TestAfterMiddleware {
//
//    //put your code here
//    public function __invoke($request, ResponseInterface $response, $next) {
//        if($request->getAttribute('alreadyAllowed')){
//            $response->getBody()->write('--yo ?--');
//            $response = $next($request, $response);
//
//            return $response;
//        }
//
//        $response->getBody()->write('--Entering M/W--');
//        $response = $next($request, $response);
//        $response->getBody()->write('--Exiting M/W--');
//
//        return $response;
//    }
//ip rule m/w testing  code
//    $response = $next($request, $response);
//        $response->getBody()->write('--Bye ip rule--');
//        if($response->hasHeader('myCustomHeader')){
//            $response->getBody()->write(json_encode($response->getHeader('myCustomHeader')));
//        }
//        return $response;
//        $response->getBody()->write('AFTER');
//----
//        if (true) {
//            $response->getBody()->write('--early bye--');
//            $response = $next($request, $response);
//            return $response;
//        }
//$response = $response->withHeader('myCustomHeader', 'customValue');
//}
