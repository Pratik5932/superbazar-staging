<?php

namespace AstraPrefixed\Http\Discovery\Strategy;

use AstraPrefixed\GuzzleHttp\Promise\Promise;
use AstraPrefixed\GuzzleHttp\Psr7\Request as GuzzleRequest;
use AstraPrefixed\Http\Client\HttpAsyncClient;
use AstraPrefixed\Http\Client\HttpClient;
use AstraPrefixed\Http\Discovery\Exception\NotFoundException;
use AstraPrefixed\Http\Discovery\MessageFactoryDiscovery;
use AstraPrefixed\Http\Discovery\Psr17FactoryDiscovery;
use AstraPrefixed\Http\Message\RequestFactory;
use AstraPrefixed\Psr\Http\Message\RequestFactoryInterface as Psr17RequestFactory;
use AstraPrefixed\Http\Message\MessageFactory;
use AstraPrefixed\Http\Message\MessageFactory\GuzzleMessageFactory;
use AstraPrefixed\Http\Message\StreamFactory;
use AstraPrefixed\Http\Message\StreamFactory\GuzzleStreamFactory;
use AstraPrefixed\Http\Message\UriFactory;
use AstraPrefixed\Http\Message\UriFactory\GuzzleUriFactory;
use AstraPrefixed\Http\Message\MessageFactory\DiactorosMessageFactory;
use AstraPrefixed\Http\Message\StreamFactory\DiactorosStreamFactory;
use AstraPrefixed\Http\Message\UriFactory\DiactorosUriFactory;
use AstraPrefixed\Psr\Http\Client\ClientInterface as Psr18Client;
use AstraPrefixed\Zend\Diactoros\Request as ZendDiactorosRequest;
use AstraPrefixed\Laminas\Diactoros\Request as DiactorosRequest;
use AstraPrefixed\Http\Message\MessageFactory\SlimMessageFactory;
use AstraPrefixed\Http\Message\StreamFactory\SlimStreamFactory;
use AstraPrefixed\Http\Message\UriFactory\SlimUriFactory;
use AstraPrefixed\Slim\Http\Request as SlimRequest;
use AstraPrefixed\GuzzleHttp\Client as GuzzleHttp;
use AstraPrefixed\Http\Adapter\Guzzle7\Client as Guzzle7;
use AstraPrefixed\Http\Adapter\Guzzle6\Client as Guzzle6;
use AstraPrefixed\Http\Adapter\Guzzle5\Client as Guzzle5;
use AstraPrefixed\Http\Client\Curl\Client as Curl;
use AstraPrefixed\Http\Client\Socket\Client as Socket;
use AstraPrefixed\Http\Adapter\React\Client as React;
use AstraPrefixed\Http\Adapter\Buzz\Client as Buzz;
use AstraPrefixed\Http\Adapter\Cake\Client as Cake;
use AstraPrefixed\Http\Adapter\Zend\Client as Zend;
use AstraPrefixed\Http\Adapter\Artax\Client as Artax;
use AstraPrefixed\Symfony\Component\HttpClient\HttplugClient as SymfonyHttplug;
use AstraPrefixed\Symfony\Component\HttpClient\Psr18Client as SymfonyPsr18;
use AstraPrefixed\Nyholm\Psr7\Factory\HttplugFactory as NyholmHttplugFactory;
/**
 * @internal
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class CommonClassesStrategy implements DiscoveryStrategy
{
    /**
     * @var array
     */
    private static $classes = [MessageFactory::class => [['class' => NyholmHttplugFactory::class, 'condition' => [NyholmHttplugFactory::class]], ['class' => GuzzleMessageFactory::class, 'condition' => [GuzzleRequest::class, GuzzleMessageFactory::class]], ['class' => DiactorosMessageFactory::class, 'condition' => [ZendDiactorosRequest::class, DiactorosMessageFactory::class]], ['class' => DiactorosMessageFactory::class, 'condition' => [DiactorosRequest::class, DiactorosMessageFactory::class]], ['class' => SlimMessageFactory::class, 'condition' => [SlimRequest::class, SlimMessageFactory::class]]], StreamFactory::class => [['class' => NyholmHttplugFactory::class, 'condition' => [NyholmHttplugFactory::class]], ['class' => GuzzleStreamFactory::class, 'condition' => [GuzzleRequest::class, GuzzleStreamFactory::class]], ['class' => DiactorosStreamFactory::class, 'condition' => [ZendDiactorosRequest::class, DiactorosStreamFactory::class]], ['class' => DiactorosStreamFactory::class, 'condition' => [DiactorosRequest::class, DiactorosStreamFactory::class]], ['class' => SlimStreamFactory::class, 'condition' => [SlimRequest::class, SlimStreamFactory::class]]], UriFactory::class => [['class' => NyholmHttplugFactory::class, 'condition' => [NyholmHttplugFactory::class]], ['class' => GuzzleUriFactory::class, 'condition' => [GuzzleRequest::class, GuzzleUriFactory::class]], ['class' => DiactorosUriFactory::class, 'condition' => [ZendDiactorosRequest::class, DiactorosUriFactory::class]], ['class' => DiactorosUriFactory::class, 'condition' => [DiactorosRequest::class, DiactorosUriFactory::class]], ['class' => SlimUriFactory::class, 'condition' => [SlimRequest::class, SlimUriFactory::class]]], HttpAsyncClient::class => [['class' => SymfonyHttplug::class, 'condition' => [SymfonyHttplug::class, Promise::class, RequestFactory::class, [self::class, 'isPsr17FactoryInstalled']]], ['class' => Guzzle7::class, 'condition' => Guzzle7::class], ['class' => Guzzle6::class, 'condition' => Guzzle6::class], ['class' => Curl::class, 'condition' => Curl::class], ['class' => React::class, 'condition' => React::class]], HttpClient::class => [['class' => SymfonyHttplug::class, 'condition' => [SymfonyHttplug::class, RequestFactory::class, [self::class, 'isPsr17FactoryInstalled']]], ['class' => Guzzle7::class, 'condition' => Guzzle7::class], ['class' => Guzzle6::class, 'condition' => Guzzle6::class], ['class' => Guzzle5::class, 'condition' => Guzzle5::class], ['class' => Curl::class, 'condition' => Curl::class], ['class' => Socket::class, 'condition' => Socket::class], ['class' => Buzz::class, 'condition' => Buzz::class], ['class' => React::class, 'condition' => React::class], ['class' => Cake::class, 'condition' => Cake::class], ['class' => Zend::class, 'condition' => Zend::class], ['class' => Artax::class, 'condition' => Artax::class], ['class' => [self::class, 'buzzInstantiate'], 'condition' => [\AstraPrefixed\Buzz\Client\FileGetContents::class, \AstraPrefixed\Buzz\Message\ResponseBuilder::class]]], Psr18Client::class => [['class' => [self::class, 'symfonyPsr18Instantiate'], 'condition' => [SymfonyPsr18::class, Psr17RequestFactory::class]], ['class' => GuzzleHttp::class, 'condition' => [self::class, 'isGuzzleImplementingPsr18']], ['class' => [self::class, 'buzzInstantiate'], 'condition' => [\AstraPrefixed\Buzz\Client\FileGetContents::class, \AstraPrefixed\Buzz\Message\ResponseBuilder::class]]]];
    /**
     * {@inheritdoc}
     */
    public static function getCandidates($type)
    {
        if (Psr18Client::class === $type) {
            return self::getPsr18Candidates();
        }
        return self::$classes[$type] ?? [];
    }
    /**
     * @return array The return value is always an array with zero or more elements. Each
     *               element is an array with two keys ['class' => string, 'condition' => mixed].
     */
    private static function getPsr18Candidates()
    {
        $candidates = self::$classes[Psr18Client::class];
        // HTTPlug 2.0 clients implements PSR18Client too.
        foreach (self::$classes[HttpClient::class] as $c) {
            try {
                if (\is_subclass_of($c['class'], Psr18Client::class)) {
                    $candidates[] = $c;
                }
            } catch (\Throwable $e) {
                \trigger_error(\sprintf('Got exception "%s (%s)" while checking if a PSR-18 Client is available', \get_class($e), $e->getMessage()), \E_USER_WARNING);
            }
        }
        return $candidates;
    }
    public static function buzzInstantiate()
    {
        return new \AstraPrefixed\Buzz\Client\FileGetContents(MessageFactoryDiscovery::find());
    }
    public static function symfonyPsr18Instantiate()
    {
        return new SymfonyPsr18(null, Psr17FactoryDiscovery::findResponseFactory(), Psr17FactoryDiscovery::findStreamFactory());
    }
    public static function isGuzzleImplementingPsr18()
    {
        return \defined('GuzzleHttp\\ClientInterface::MAJOR_VERSION');
    }
    /**
     * Can be used as a condition.
     *
     * @return bool
     */
    public static function isPsr17FactoryInstalled()
    {
        try {
            Psr17FactoryDiscovery::findResponseFactory();
        } catch (NotFoundException $e) {
            return \false;
        } catch (\Throwable $e) {
            \trigger_error(\sprintf('Got exception "%s (%s)" while checking if a PSR-17 ResponseFactory is available', \get_class($e), $e->getMessage()), \E_USER_WARNING);
            return \false;
        }
        return \true;
    }
}
