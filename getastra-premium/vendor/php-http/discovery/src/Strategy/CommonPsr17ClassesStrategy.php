<?php

namespace AstraPrefixed\Http\Discovery\Strategy;

use AstraPrefixed\Psr\Http\Message\RequestFactoryInterface;
use AstraPrefixed\Psr\Http\Message\ResponseFactoryInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestFactoryInterface;
use AstraPrefixed\Psr\Http\Message\StreamFactoryInterface;
use AstraPrefixed\Psr\Http\Message\UploadedFileFactoryInterface;
use AstraPrefixed\Psr\Http\Message\UriFactoryInterface;
/**
 * @internal
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CommonPsr17ClassesStrategy implements DiscoveryStrategy
{
    /**
     * @var array
     */
    private static $classes = [RequestFactoryInterface::class => ['AstraPrefixed\\Phalcon\\Http\\Message\\RequestFactory', 'AstraPrefixed\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'AstraPrefixed\\Zend\\Diactoros\\RequestFactory', 'AstraPrefixed\\GuzzleHttp\\Psr7\\HttpFactory', 'AstraPrefixed\\Http\\Factory\\Diactoros\\RequestFactory', 'AstraPrefixed\\Http\\Factory\\Guzzle\\RequestFactory', 'AstraPrefixed\\Http\\Factory\\Slim\\RequestFactory', 'AstraPrefixed\\Laminas\\Diactoros\\RequestFactory', 'AstraPrefixed\\Slim\\Psr7\\Factory\\RequestFactory'], ResponseFactoryInterface::class => ['AstraPrefixed\\Phalcon\\Http\\Message\\ResponseFactory', 'AstraPrefixed\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'AstraPrefixed\\Zend\\Diactoros\\ResponseFactory', 'AstraPrefixed\\GuzzleHttp\\Psr7\\HttpFactory', 'AstraPrefixed\\Http\\Factory\\Diactoros\\ResponseFactory', 'AstraPrefixed\\Http\\Factory\\Guzzle\\ResponseFactory', 'AstraPrefixed\\Http\\Factory\\Slim\\ResponseFactory', 'AstraPrefixed\\Laminas\\Diactoros\\ResponseFactory', 'AstraPrefixed\\Slim\\Psr7\\Factory\\ResponseFactory'], ServerRequestFactoryInterface::class => ['AstraPrefixed\\Phalcon\\Http\\Message\\ServerRequestFactory', 'AstraPrefixed\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'AstraPrefixed\\Zend\\Diactoros\\ServerRequestFactory', 'AstraPrefixed\\GuzzleHttp\\Psr7\\HttpFactory', 'AstraPrefixed\\Http\\Factory\\Diactoros\\ServerRequestFactory', 'AstraPrefixed\\Http\\Factory\\Guzzle\\ServerRequestFactory', 'AstraPrefixed\\Http\\Factory\\Slim\\ServerRequestFactory', 'AstraPrefixed\\Laminas\\Diactoros\\ServerRequestFactory', 'AstraPrefixed\\Slim\\Psr7\\Factory\\ServerRequestFactory'], StreamFactoryInterface::class => ['AstraPrefixed\\Phalcon\\Http\\Message\\StreamFactory', 'AstraPrefixed\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'AstraPrefixed\\Zend\\Diactoros\\StreamFactory', 'AstraPrefixed\\GuzzleHttp\\Psr7\\HttpFactory', 'AstraPrefixed\\Http\\Factory\\Diactoros\\StreamFactory', 'AstraPrefixed\\Http\\Factory\\Guzzle\\StreamFactory', 'AstraPrefixed\\Http\\Factory\\Slim\\StreamFactory', 'AstraPrefixed\\Laminas\\Diactoros\\StreamFactory', 'AstraPrefixed\\Slim\\Psr7\\Factory\\StreamFactory'], UploadedFileFactoryInterface::class => ['AstraPrefixed\\Phalcon\\Http\\Message\\UploadedFileFactory', 'AstraPrefixed\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'AstraPrefixed\\Zend\\Diactoros\\UploadedFileFactory', 'AstraPrefixed\\GuzzleHttp\\Psr7\\HttpFactory', 'AstraPrefixed\\Http\\Factory\\Diactoros\\UploadedFileFactory', 'AstraPrefixed\\Http\\Factory\\Guzzle\\UploadedFileFactory', 'AstraPrefixed\\Http\\Factory\\Slim\\UploadedFileFactory', 'AstraPrefixed\\Laminas\\Diactoros\\UploadedFileFactory', 'AstraPrefixed\\Slim\\Psr7\\Factory\\UploadedFileFactory'], UriFactoryInterface::class => ['AstraPrefixed\\Phalcon\\Http\\Message\\UriFactory', 'AstraPrefixed\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'AstraPrefixed\\Zend\\Diactoros\\UriFactory', 'AstraPrefixed\\GuzzleHttp\\Psr7\\HttpFactory', 'AstraPrefixed\\Http\\Factory\\Diactoros\\UriFactory', 'AstraPrefixed\\Http\\Factory\\Guzzle\\UriFactory', 'AstraPrefixed\\Http\\Factory\\Slim\\UriFactory', 'AstraPrefixed\\Laminas\\Diactoros\\UriFactory', 'AstraPrefixed\\Slim\\Psr7\\Factory\\UriFactory']];
    /**
     * {@inheritdoc}
     */
    public static function getCandidates($type)
    {
        $candidates = [];
        if (isset(self::$classes[$type])) {
            foreach (self::$classes[$type] as $class) {
                $candidates[] = ['class' => $class, 'condition' => [$class]];
            }
        }
        return $candidates;
    }
}
