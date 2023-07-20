<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient\GatekeeperUpdateJob;

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
 * Description of GatekeeperUpdateJobApi.
 *
 * @author aditya
 */
class GatekeeperUpdateJobApi
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
     * Operation patchGatekeeperUpdateJobItem.
     *
     * Updates the GatekeeperUpdateJob resource.
     *
     * @param string                                                                 $id                                                id (required)
     * @param \GetAstra\Api\Client\Model\GatekeeperUpdateJobGatekeeperUpdateJobInput $gatekeeper_update_job_gatekeeper_update_job_input The updated GatekeeperUpdateJob resource (optional)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return \GetAstra\Api\Client\Model\GatekeeperUpdateJobJsonldGatekeeperUpdateJobOutput
     */
    public function patchGatekeeperUpdateJobItem($id, $gatekeeper_update_job_gatekeeper_update_job_input = null)
    {
        list($response) = $this->patchGatekeeperUpdateJobItemWithHttpInfo($id, $gatekeeper_update_job_gatekeeper_update_job_input);
        return $response;
    }
    /**
     * Operation patchGatekeeperUpdateJobItemWithHttpInfo.
     *
     * Updates the GatekeeperUpdateJob resource.
     *
     * @param string                                                                 $id                                                (required)
     * @param \GetAstra\Api\Client\Model\GatekeeperUpdateJobGatekeeperUpdateJobInput $gatekeeper_update_job_gatekeeper_update_job_input The updated GatekeeperUpdateJob resource (optional)
     *
     * @throws \GetAstra\Client\Tclient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     *
     * @return array of \GetAstra\Api\Client\Model\GatekeeperUpdateJobJsonldGatekeeperUpdateJobOutput, HTTP status code, HTTP response headers (array of strings)
     */
    public function patchGatekeeperUpdateJobItemWithHttpInfo($id, $gatekeeper_update_job_gatekeeper_update_job_input = null)
    {
        $request = $this->patchGatekeeperUpdateJobItemRequest($id, $gatekeeper_update_job_gatekeeper_update_job_input);
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
                    if ('\\GetAstra\\Api\\Client\\Model\\GatekeeperUpdateJobJsonldGatekeeperUpdateJobOutput' === '\\SplFileObject') {
                        $content = $responseBody;
                        //stream goes to serializer
                    } else {
                        $content = (string) $responseBody;
                    }
                    return [$content, $response->getStatusCode(), $response->getHeaders()];
            }
            $returnType = 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\GatekeeperUpdateJobJsonldGatekeeperUpdateJobOutput';
            $responseBody = $response->getBody();
            if ('\\SplFileObject' === $returnType) {
                $content = $responseBody;
                //stream goes to serializer
            } else {
                $content = (string) $responseBody;
            }
            return [$content, $response->getStatusCode(), $response->getHeaders()];
            // return [
            //     ObjectSerializer::deserialize($content, $returnType, []),
            //     $response->getStatusCode(),
            //     $response->getHeaders(),
            // ];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize($e->getResponseBody(), 'AstraPrefixed\\GetAstra\\Api\\Client\\Model\\GatekeeperUpdateJobJsonldGatekeeperUpdateJobOutput', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }
    /**
     * Create request for operation 'patchGatekeeperUpdateJobItem'.
     *
     * @param string                                                                 $id                                                (required)
     * @param \GetAstra\Api\Client\Model\GatekeeperUpdateJobGatekeeperUpdateJobInput $gatekeeper_update_job_gatekeeper_update_job_input The updated GatekeeperUpdateJob resource (optional)
     *
     * @throws \InvalidArgumentException
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function patchGatekeeperUpdateJobItemRequest($id, $gatekeeper_update_job_gatekeeper_update_job_input = null)
    {
        // verify the required parameter 'id' is set
        if (null === $id || \is_array($id) && 0 === \count($id)) {
            throw new \InvalidArgumentException('Missing the required parameter $id when calling patchGatekeeperUpdateJobItem');
        }
        $resourcePath = '/api/waf/client/update-jobs/{id}';
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
        if (isset($gatekeeper_update_job_gatekeeper_update_job_input)) {
            $_tempBody = $gatekeeper_update_job_gatekeeper_update_job_input;
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
