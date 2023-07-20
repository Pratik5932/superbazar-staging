<?php

/**
 * This file is part of the Astra Security Suite.
 *
 *  Copyright (c) 2019 (https://www.getastra.com/)
 *
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
/**
 * @author HumansofAstra-WZ <help@getastra.com>
 * @date   2019-03-15
 */
namespace AstraPrefixed\GetAstra\Client\Helper;

use AstraPrefixed\GeoIp2\Database\Reader;
class UrlHelper
{
    //private $ipReader;
    public function __construct()
    {
        //$this->ipReader = new Reader(ASTRAROOT.'GeoLite2-Country.mmdb');
    }
    public static function getCurrentUri($params = null, $no_trailing_slash = \true, $remove_query_string = \false)
    {
        // http(s)://domain.com
        $url = 'on' == @$_SERVER['HTTPS'] || 'on' == @$_SERVER['HTTP_X_FORWARDED_SSL'] ? 'https://' . $_SERVER['SERVER_NAME'] : 'http://' . $_SERVER['SERVER_NAME'];
        // add the port number, if there is one
        if (\strpos($url, ':') === \false && '80' != @$_SERVER['SERVER_PORT'] && '443' != @$_SERVER['SERVER_PORT']) {
            $url .= ':' . @$_SERVER['SERVER_PORT'];
        }
        // Append the query string
        if (isset($_SERVER['X_ASTRA_ORIGINAL_REQUEST_URI'])) {
            $url .= $_SERVER['X_ASTRA_ORIGINAL_REQUEST_URI'];
        } else {
            $url .= $_SERVER['REQUEST_URI'];
        }
        // [optionally] remove the trailing slash if there is one
        if ($no_trailing_slash) {
            $url = \rtrim($url, '/');
        }
        // Add any additional parameters that may have been included
        if ($params) {
            $url .= $params;
        }
        if ($remove_query_string) {
            $url = \strtok($url, '?');
        }
        return $url;
    }
    public static function getDashboardUri($slug = '', $params = array())
    {
        return \getenv('ASTRA_DASHBOARD_URL_HTTPS') . $slug . \http_build_query($params);
    }
    // Function to get the client IP address
    public function getClientIp()
    {
        $serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_SUCURI_CLIENTIP', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (\true === \array_key_exists($key, $_SERVER)) {
                foreach (\explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = \trim($ip);
                    // attempt to validate IP
                    if (($this->validate_ip($ip) || $this->validate_ipv6($ip)) && $ip !== $serverIp) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : \false;
    }
    private function validate_ipv6($ip)
    {
        if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            return \true;
        }
        return \false;
    }
    private function validate_ip($ip)
    {
        if (\false === \filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 | \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE)) {
            return \false;
        }
        return \true;
    }
    /**
     * @param string $ip
     *
     * @return object $record
     */
    // public function getIpInfo($ip)
    // {
    //     try {
    //         $record = $this->ipReader->country($ip);
    //         return $record;
    //     } catch (\Exception $e) {
    //         return null;
    //     }
    // }
}
