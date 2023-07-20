<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient\WafRule;

use AstraPrefixed\GetAstra\Client\Tclient\ApiException;
use AstraPrefixed\GetAstra\Client\Tclient\Configuration;
use AstraPrefixed\GetAstra\Client\Tclient\HeaderSelector;
use AstraPrefixed\GetAstra\Client\Tclient\ObjectSerializer;
use AstraPrefixed\GuzzleHttp\Client;
use AstraPrefixed\GuzzleHttp\ClientInterface;
use AstraPrefixed\GuzzleHttp\Exception\RequestException;
use AstraPrefixed\GuzzleHttp\Psr7\MultipartStream;
use AstraPrefixed\GuzzleHttp\Psr7\Request;
use AstraPrefixed\GuzzleHttp\RequestOptions;
/**
 * Description of WafRuleApi.
 *
 * @author aditya
 */
class WafRuleApi
{
    /**
     * @var ClientInterface
     */
    protected $client;
    /**
     * @var Configuration
     */
    protected $config;
    /**
     * @var HeaderSelector
     */
    protected $headerSelector;
    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     */
    public function __construct(ClientInterface $client = null, Configuration $config = null, HeaderSelector $selector = null)
    {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
    }
    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * Operation getWafRuleCollection.
     *
     * Retrieves the collection of WafRule resources.
     *
     * @param string $tags           tags (optional)
     * @param int    $impact         impact (optional)
     * @param bool   $log_only       log_only (optional)
     * @param string $evaluator      evaluator (optional)
     * @param string $severity       severity (optional)
     * @param string $source         source (optional)
     * @param int    $page           The collection page number (optional, default to 1)
     * @param int    $items_per_page The number of items per page (optional, default to 30)
     *
     * @throws \Swagger\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return \Swagger\Client\Model\InlineResponse20044
     */
    public function getWafRuleCollection($tags = null, $impact = null, $log_only = null, $evaluator = null, $severity = null, $source = null, $page = '1', $items_per_page = '30')
    {
        list($response) = $this->getWafRuleCollectionWithHttpInfo($tags, $impact, $log_only, $evaluator, $severity, $source, $page, $items_per_page);
        return $response;
    }
    /**
     * Operation getWafRuleCollectionWithHttpInfo.
     *
     * Retrieves the collection of WafRule resources.
     *
     * @param string $tags           (optional)
     * @param int    $impact         (optional)
     * @param bool   $log_only       (optional)
     * @param string $evaluator      (optional)
     * @param string $severity       (optional)
     * @param string $source         (optional)
     * @param int    $page           The collection page number (optional, default to 1)
     * @param int    $items_per_page The number of items per page (optional, default to 30)
     *
     * @throws \Swagger\Client\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return array of \Swagger\Client\Model\InlineResponse20044, HTTP status code, HTTP response headers (array of strings)
     */
    public function getWafRuleCollectionWithHttpInfo($tags = null, $impact = null, $log_only = null, $evaluator = null, $severity = null, $source = null, $page = '1', $items_per_page = '30')
    {
        $returnType = 'AstraPrefixed\\Swagger\\Client\\Model\\InlineResponse20044';
        $request = $this->getWafRuleCollectionRequest($tags, $impact, $log_only, $evaluator, $severity, $source, $page, $items_per_page);
        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException("[{$e->getCode()}] {$e->getMessage()}", $e->getCode(), $e->getResponse() ? $e->getResponse()->getHeaders() : null, $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null);
            }
            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(\sprintf('[%d] Error connecting to the API (%s)', $statusCode, $request->getUri()), $statusCode, $response->getHeaders(), $response->getBody());
            }
            $responseBody = $response->getBody();
            if ('\\SplFileObject' === $returnType) {
                $content = $responseBody;
                //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!\in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = \json_decode($content, \true);
                }
            }
            return [$content, $response->getStatusCode(), $response->getHeaders()];
            //            return [
            //                ObjectSerializer::deserialize($content, $returnType, []),
            //                $response->getStatusCode(),
            //                $response->getHeaders()
            //            ];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize($e->getResponseBody(), 'AstraPrefixed\\Swagger\\Client\\Model\\InlineResponse20044', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }
    /**
     * Create request for operation 'getWafRuleCollection'.
     *
     * @param string $tags           (optional)
     * @param int    $impact         (optional)
     * @param bool   $log_only       (optional)
     * @param string $evaluator      (optional)
     * @param string $severity       (optional)
     * @param string $source         (optional)
     * @param int    $page           The collection page number (optional, default to 1)
     * @param int    $items_per_page The number of items per page (optional, default to 30)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getWafRuleCollectionRequest($tags = null, $impact = null, $log_only = null, $evaluator = null, $severity = null, $source = null, $page = '1', $items_per_page = '30')
    {
        $resourcePath = '/api/waf/rules';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = \false;
        // query params
        if (null !== $tags) {
            $queryParams['tags'] = ObjectSerializer::toQueryValue($tags, null);
        }
        // query params
        if (null !== $impact) {
            $queryParams['impact'] = ObjectSerializer::toQueryValue($impact, null);
        }
        // query params
        if (null !== $log_only) {
            $queryParams['logOnly'] = ObjectSerializer::toQueryValue($log_only, null);
        }
        // query params
        if (null !== $evaluator) {
            $queryParams['evaluator'] = ObjectSerializer::toQueryValue($evaluator, null);
        }
        // query params
        if (null !== $severity) {
            $queryParams['severity'] = ObjectSerializer::toQueryValue($severity, null);
        }
        // query params
        if (null !== $source) {
            $queryParams['source'] = ObjectSerializer::toQueryValue($source, null);
        }
        // query params
        if (null !== $page) {
            $queryParams['page'] = ObjectSerializer::toQueryValue($page, null);
        }
        // query params
        //if (null !== $items_per_page) {
        $queryParams['pagination'] = ObjectSerializer::toQueryValue(\false);
        //}
        // body params
        $_tempBody = null;
        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(['application/ld+json', 'application/json', 'text/html']);
        } else {
            $headers = $this->headerSelector->selectHeaders(['application/ld+json', 'application/json', 'text/html'], []);
        }
        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && 'application/json' === $headers['Content-Type']) {
                $httpBody = \AstraPrefixed\GuzzleHttp\json_encode($httpBody);
            }
        } elseif (\count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = ['name' => $formParamName, 'contents' => $formParamValue];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);
            } elseif ('application/json' === $headers['Content-Type']) {
                $httpBody = \AstraPrefixed\GuzzleHttp\json_encode($formParams);
            } else {
                // for HTTP post (form)
                $httpBody = \AstraPrefixed\GuzzleHttp\Psr7\build_query($formParams);
            }
        }
        // this endpoint requires OAuth (access token)
        if (null !== $this->config->getAccessToken()) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }
        $headers = \array_merge($defaultHeaders, $headerParams, $headers);
        $query = \AstraPrefixed\GuzzleHttp\Psr7\build_query($queryParams);
        return new Request('GET', $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''), $headers, $httpBody);
    }
    /**
     * Create http client option.
     *
     * @throws \RuntimeException on file opening failure
     *
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = \fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }
        return $options;
    }
}
