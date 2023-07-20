<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Tclient;

/**
 * Description of HeaderSelector.
 *
 * @author aditya
 */
class HeaderSelector
{
    /**
     * @param string[] $accept
     * @param string[] $contentTypes
     *
     * @return array
     */
    public function selectHeaders($accept, $contentTypes)
    {
        $headers = [];
        $accept = $this->selectAcceptHeader($accept);
        if (null !== $accept) {
            $headers['Accept'] = $accept;
        }
        $headers['Content-Type'] = $this->selectContentTypeHeader($contentTypes);
        return $headers;
    }
    /**
     * @param string[] $accept
     *
     * @return array
     */
    public function selectHeadersForMultipart($accept)
    {
        $headers = $this->selectHeaders($accept, []);
        unset($headers['Content-Type']);
        return $headers;
    }
    /**
     * Return the header 'Accept' based on an array of Accept provided.
     *
     * @param string[] $accept Array of header
     *
     * @return string Accept (e.g. application/json)
     */
    private function selectAcceptHeader($accept)
    {
        if (0 === \count($accept) || 1 === \count($accept) && '' === $accept[0]) {
            return null;
        } elseif ($jsonAccept = \preg_grep('~(?i)^(application/json|[^;/ \\t]+/[^;/ \\t]+[+]json)[ \\t]*(;.*)?$~', $accept)) {
            return \implode(',', $jsonAccept);
        } else {
            return \implode(',', $accept);
        }
    }
    /**
     * Return the content type based on an array of content-type provided.
     *
     * @param string[] $contentType Array fo content-type
     *
     * @return string Content-Type (e.g. application/json)
     */
    private function selectContentTypeHeader($contentType)
    {
        if (0 === \count($contentType) || 1 === \count($contentType) && '' === $contentType[0]) {
            return 'application/json';
        } elseif (\preg_grep("/application\\/json/i", $contentType)) {
            return 'application/json';
        } else {
            return \implode(',', $contentType);
        }
    }
}