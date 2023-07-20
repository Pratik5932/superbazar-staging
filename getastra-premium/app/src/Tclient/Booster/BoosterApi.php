<?php

namespace AstraPrefixed\GetAstra\Client\Tclient\Booster;

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
 * BoosterApi Class Doc Comment.
 *
 * @category Class
 *
 * @author   OpenAPI Generator team
 *
 * @see     https://openapi-generator.tech
 */
class BoosterApi
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
     * @var int Host index
     */
    protected $hostIndex;
    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $host_index (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(ClientInterface $client = null, Configuration $config = null, HeaderSelector $selector = null, $host_index = 0)
    {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $host_index;
    }
    /**
     * Set the host index.
     *
     * @param  int Host index (required)
     */
    public function setHostIndex($host_index)
    {
        $this->hostIndex = $host_index;
    }
    /**
     * Get the host index.
     *
     * @return Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }
    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * Operation getBoosterCollection.
     *
     * Retrieves the collection of Booster resources.
     *
     * @param string   $name             name (optional)
     * @param string   $site             site (optional)
     * @param string[] $site2            site2 (optional)
     * @param string   $order_created_at order_created_at (optional)
     * @param int      $page             The collection page number (optional, default to 1)
     * @param int      $items_per_page   The number of items per page (optional, default to 30)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return \GetAstra\Client\Tclient\Booster\BoosterJsonldWafBoosterOutput
     */
    public function getBoosterCollection($name = null, $site = null, $site2 = null, $order_created_at = null, $page = 1, $items_per_page = 30)
    {
        list($response) = $this->getBoosterCollectionWithHttpInfo($name, $site, $site2, $order_created_at, $page, $items_per_page);
        return $response;
    }
    /**
     * Operation getBoosterCollectionWithHttpInfo.
     *
     * Retrieves the collection of Booster resources.
     *
     * @param string   $name             (optional)
     * @param string   $site             (optional)
     * @param string[] $site2            (optional)
     * @param string   $order_created_at (optional)
     * @param int      $page             The collection page number (optional, default to 1)
     * @param int      $items_per_page   The number of items per page (optional, default to 30)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return array of \GetAstra\Client\Tclient\Booster\BoosterJsonldWafBoosterOutput, HTTP status code, HTTP response headers (array of strings)
     */
    public function getBoosterCollectionWithHttpInfo($name = null, $site = null, $site2 = null, $order_created_at = null, $page = 1, $items_per_page = 30)
    {
        $request = $this->getBoosterCollectionRequest($name, $site, $site2, $order_created_at, $page, $items_per_page);
        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException("[{$e->getCode()}] {$e->getMessage()}", $e->getCode(), $e->getResponse() ? $e->getResponse()->getHeaders() : null, $e->getResponse() ? (string) $e->getResponse()->getBody() : null);
            }
            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(\sprintf('[%d] Error connecting to the API (%s)', $statusCode, $request->getUri()), $statusCode, $response->getHeaders(), $response->getBody());
            }
            $responseBody = $response->getBody();
            switch ($statusCode) {
                case 200:
                    if ('\\GetAstra\\Api\\Client\\Model\\InlineResponse20034' === '\\SplFileObject') {
                        $content = $responseBody;
                        //stream goes to serializer
                    } else {
                        $content = (string) $responseBody;
                    }
                    //serializer doesn't work so just return $content
                    return [$content, $response->getStatusCode(), $response->getHeaders()];
            }
            $returnType = 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\InlineResponse20034';
            $responseBody = $response->getBody();
            if ('\\SplFileObject' === $returnType) {
                $content = $responseBody;
                //stream goes to serializer
            } else {
                $content = (string) $responseBody;
            }
            return [$content, $response->getStatusCode(), $response->getHeaders()];
            //            return [
            //                ObjectSerializer::deserialize($content, $returnType, []),
            //                $response->getStatusCode(),
            //                $response->getHeaders(),
            //            ];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize($e->getResponseBody(), 'AstraPrefixed\\GetAstra\\Client\\Tclient\\Booster\\BoosterJsonldWafBoosterOutput', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }
    /**
     * Create request for operation 'getBoosterCollection'.
     *
     * @param string   $name             (optional)
     * @param string   $site             (optional)
     * @param string[] $site2            (optional)
     * @param string   $order_created_at (optional)
     * @param int      $page             The collection page number (optional, default to 1)
     * @param int      $items_per_page   The number of items per page (optional, default to 30)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getBoosterCollectionRequest($name = null, $site = null, $site2 = null, $order_created_at = null, $page = 1, $items_per_page = 30)
    {
        if (null !== $items_per_page && $items_per_page < 0) {
            throw new \InvalidArgumentException('invalid value for "$items_per_page" when calling BoosterApi.getBoosterCollection, must be bigger than or equal to 0.');
        }
        $resourcePath = '/api/waf/boosters';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = \false;
        // query params
        if (null !== $name) {
            if ('form' === 'form' && \is_array($name)) {
                foreach ($name as $key => $value) {
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams['name'] = $name;
            }
        }
        // query params
        if (null !== $site) {
            if ('form' === 'form' && \is_array($site)) {
                foreach ($site as $key => $value) {
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams['site'] = $site;
            }
        }
        // query params
        if (null !== $site2) {
            if ('form' === 'form' && \is_array($site2)) {
                foreach ($site2 as $key => $value) {
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams['site[]'] = $site2;
            }
        }
        // query params
        if (null !== $order_created_at) {
            if ('form' === 'form' && \is_array($order_created_at)) {
                foreach ($order_created_at as $key => $value) {
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams['order[createdAt]'] = $order_created_at;
            }
        }
        // query params
        if (null !== $page) {
            if ('form' === 'form' && \is_array($page)) {
                foreach ($page as $key => $value) {
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams['page'] = $page;
            }
        }
        // query params
        //        if (null !== $items_per_page) {
        //            if ('form' === 'form' && is_array($items_per_page)) {
        //                foreach ($items_per_page as $key => $value) {
        //                    $queryParams[$key] = $value;
        //                }
        //            } else {
        //                $queryParams['itemsPerPage'] = $items_per_page;
        //            }
        //        }
        $queryParams['pagination'] = ObjectSerializer::toQueryValue(\false);
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
            if ('application/json' === $headers['Content-Type']) {
                $httpBody = \AstraPrefixed\GuzzleHttp\json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody));
            } else {
                $httpBody = $_tempBody;
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
