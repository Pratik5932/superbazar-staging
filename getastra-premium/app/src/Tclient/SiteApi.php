<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient;

use AstraPrefixed\GetAstra\Client\Tclient\ApiException;
use AstraPrefixed\GuzzleHttp\Client;
use AstraPrefixed\GuzzleHttp\ClientInterface;
use AstraPrefixed\GuzzleHttp\Exception\RequestException;
use AstraPrefixed\GuzzleHttp\Psr7\MultipartStream;
use AstraPrefixed\GuzzleHttp\Psr7\Request;
use AstraPrefixed\GuzzleHttp\RequestOptions;
/**
 * Description of SiteApi.
 *
 * @author aditya
 */
class SiteApi
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
     * Operation getSiteSettingsSiteItem.
     *
     * Get a setting value from the Site object
     *
     * @param string $id                 Site ID (required)
     * @param string $settings_namespace Setting namespace - exclude for all settings (optional)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return \GetAstra\Client\Tclient\SiteWafSiteOutput
     */
    public function getSiteSettingsSiteItem($id, $settings_namespace = null)
    {
        list($response) = $this->getSiteSettingsSiteItemWithHttpInfo($id, $settings_namespace);
        //var_dump($response);exit;
        return $response;
    }
    public function getSiteSettingsSiteItemWithHttpInfo($id, $settings_namespace = null)
    {
        $request = $this->getSiteSettingsSiteItemRequest($id, $settings_namespace);
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
                    if ('\\GetAstra\\Client\\Tclient\\SiteWafSiteOutput' === '\\SplFileObject') {
                        $content = $responseBody;
                        //stream goes to serializer
                    } else {
                        $content = (string) $responseBody;
                    }
                    return [$content, $response->getStatusCode(), $response->getHeaders()];
            }
            $returnType = 'AstraPrefixed\\GetAstra\\Client\\Tclient\\SiteWafSiteOutput';
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
            //                $response->getHeaders()
            //            ];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize($e->getResponseBody(), 'AstraPrefixed\\GetAstra\\Client\\Tclient\\SiteWafSiteOutput', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }
    /**
     * Create request for operation 'getSiteSettingsSiteItem'.
     *
     * @param string $id                 Site ID (required)
     * @param string $settings_namespace Setting namespace - exclude for all settings (optional)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getSiteSettingsSiteItemRequest($id, $settings_namespace = null)
    {
        // verify the required parameter 'id' is set
        if (null === $id || \is_array($id) && 0 === \count($id)) {
            throw new \InvalidArgumentException('Missing the required parameter $id when calling getSiteSettingsSiteItem');
        }
        $resourcePath = '/api/waf/sites/{id}/settings';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = \false;
        // query params
        if (null !== $settings_namespace) {
            if ('form' === 'form' && \is_array($settings_namespace)) {
                foreach ($settings_namespace as $key => $value) {
                    $queryParams[$key] = $value;
                }
            } else {
                $queryParams['settings_namespace'] = $settings_namespace;
            }
        }
        // path params
        if (null !== $id) {
            $resourcePath = \str_replace('{' . 'id' . '}', ObjectSerializer::toPathValue($id), $resourcePath);
        }
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
    /**
     * Operation getSiteItem.
     *
     * Retrieves a Site resource.
     *
     * @param string $id id (required)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return \GetAstra\Api\Client\Model\SiteJsonld
     */
    public function getSiteItem($id)
    {
        list($response) = $this->getSiteItemWithHttpInfo($id);
        return $response;
    }
    /**
     * Operation getSiteItemWithHttpInfo.
     *
     * Retrieves a Site resource.
     *
     * @param string $id (required)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return array of \GetAstra\Api\Client\Model\SiteJsonld, HTTP status code, HTTP response headers (array of strings)
     */
    public function getSiteItemWithHttpInfo($id)
    {
        $request = $this->getSiteItemRequest($id);
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
                    if ('\\GetAstra\\Api\\Client\\Model\\SiteJsonld' === '\\SplFileObject') {
                        $content = $responseBody;
                        //stream goes to serializer
                    } else {
                        $content = (string) $responseBody;
                    }
                    return [$content, $response->getStatusCode(), $response->getHeaders()];
            }
            $returnType = 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\SiteJsonld';
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
                    $data = ObjectSerializer::deserialize($e->getResponseBody(), 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\SiteJsonld', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }
    /**
     * Create request for operation 'getSiteItem'.
     *
     * @param string $id (required)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getSiteItemRequest($id)
    {
        // verify the required parameter 'id' is set
        if (null === $id || \is_array($id) && 0 === \count($id)) {
            throw new \InvalidArgumentException('Missing the required parameter $id when calling getSiteItem');
        }
        $resourcePath = '/api/waf/sites/{id}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = \false;
        // path params
        if (null !== $id) {
            $resourcePath = \str_replace('{' . 'id' . '}', ObjectSerializer::toPathValue($id), $resourcePath);
        }
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
     * Operation getSiteSettingsSiteItemAsync.
     *
     * Get a setting value from the Site object
     *
     * @param string   $id                 Site ID (required)
     * @param string   $settings_namespace Setting namespace - exclude for all settings (optional)
     * @param string[] $request_body       Optional list of keys to return - returns all keys by default (optional)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getSiteSettingsSiteItemAsync($id, $settings_namespace = null, $request_body = null)
    {
        return $this->getSiteSettingsSiteItemAsyncWithHttpInfo($id, $settings_namespace, $request_body)->then(function ($response) {
            return $response[0];
        });
    }
    /**
     * Operation getSiteSettingsSiteItemAsyncWithHttpInfo.
     *
     * Get a setting value from the Site object
     *
     * @param string   $id                 Site ID (required)
     * @param string   $settings_namespace Setting namespace - exclude for all settings (optional)
     * @param string[] $request_body       Optional list of keys to return - returns all keys by default (optional)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getSiteSettingsSiteItemAsyncWithHttpInfo($id, $settings_namespace = null, $request_body = null)
    {
        $returnType = 'OneOfSiteSettingsUploadScannerSettingsMalwareScannerSettingsSlackSettingsObject';
        $request = $this->getSiteSettingsSiteItemRequest($id, $settings_namespace, $request_body);
        return $this->client->sendAsync($request, $this->createHttpClientOption())->then(function ($response) use($returnType) {
            $responseBody = $response->getBody();
            if ('\\SplFileObject' === $returnType) {
                $content = $responseBody;
                //stream goes to serializer
            } else {
                $content = (string) $responseBody;
            }
            return [$content, $response->getStatusCode(), $response->getHeaders()];
            //                    return [
            //                        ObjectSerializer::deserialize($content, $returnType, []),
            //                        $response->getStatusCode(),
            //                        $response->getHeaders(),
            //                    ];
        }, function ($exception) {
            $response = $exception->getResponse();
            $statusCode = $response->getStatusCode();
            throw new ApiException(\sprintf('[%d] Error connecting to the API (%s)', $statusCode, $exception->getRequest()->getUri()), $statusCode, $response->getHeaders(), $response->getBody());
        });
    }
    /**
     * Operation patchSiteItem.
     *
     * Updates the Site resource.
     *
     * @param string                                      $id                  id (required)
     * @param \GetAstra\Api\Client\Model\SiteWafSiteInput $site_waf_site_input The updated Site resource (optional)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return \GetAstra\Api\Client\Model\SiteJsonldWafSiteOutput
     */
    public function patchSiteItem($id, $site_waf_site_input = null)
    {
        list($response) = $this->patchSiteItemWithHttpInfo($id, $site_waf_site_input);
        return $response;
    }
    /**
     * Operation patchSiteItemWithHttpInfo.
     *
     * Updates the Site resource.
     *
     * @param string                                      $id                  (required)
     * @param \GetAstra\Api\Client\Model\SiteWafSiteInput $site_waf_site_input The updated Site resource (optional)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return array of \GetAstra\Api\Client\Model\SiteJsonldWafSiteOutput, HTTP status code, HTTP response headers (array of strings)
     */
    public function patchSiteItemWithHttpInfo($id, $site_waf_site_input = null)
    {
        $request = $this->patchSiteItemRequest($id, $site_waf_site_input);
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
                    if ('\\GetAstra\\Api\\Client\\Model\\SiteJsonldWafSiteOutput' === '\\SplFileObject') {
                        $content = $responseBody;
                        //stream goes to serializer
                    } else {
                        $content = (string) $responseBody;
                    }
                    return [$content, $response->getStatusCode(), $response->getHeaders()];
            }
            $returnType = 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\SiteJsonldWafSiteOutput';
            $responseBody = $response->getBody();
            if ('\\SplFileObject' === $returnType) {
                $content = $responseBody;
                //stream goes to serializer
            } else {
                $content = (string) $responseBody;
            }
            return [$content, $response->getStatusCode(), $response->getHeaders()];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    //                    $data = ObjectSerializer::deserialize(
                    //                        $e->getResponseBody(),
                    //                        '\GetAstra\Api\Client\Model\SiteJsonldWafSiteOutput',
                    //                        $e->getResponseHeaders()
                    //                    );
                    $e->setResponseObject($e->getResponseBody());
                    break;
            }
            throw $e;
        }
    }
    /**
     * Create request for operation 'patchSiteItem'.
     *
     * @param string                                      $id                  (required)
     * @param \GetAstra\Api\Client\Model\SiteWafSiteInput $site_waf_site_input The updated Site resource (optional)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function patchSiteItemRequest($id, $site_waf_site_input = null)
    {
        // verify the required parameter 'id' is set
        if (null === $id || \is_array($id) && 0 === \count($id)) {
            throw new \InvalidArgumentException('Missing the required parameter $id when calling patchSiteItem');
        }
        $resourcePath = '/api/waf/sites/{id}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = \false;
        // path params
        if (null !== $id) {
            $resourcePath = \str_replace('{' . 'id' . '}', ObjectSerializer::toPathValue($id), $resourcePath);
        }
        // body params
        $_tempBody = null;
        if (isset($site_waf_site_input)) {
            $_tempBody = $site_waf_site_input;
        }
        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(['application/ld+json', 'application/json', 'text/html']);
        } else {
            $headers = $this->headerSelector->selectHeaders(['application/ld+json', 'application/json', 'text/html'], ['application/merge-patch+json']);
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
        return new Request('PATCH', $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''), $headers, $httpBody);
    }
}
