<?php

namespace AstraPrefixed\GetAstra\Client\Helper;

class ServerHelper
{
    public function supportsSSL() : bool
    {
        $version = \curl_version();
        if ($version['features'] & \CURL_VERSION_SSL) {
            return \true;
        }
        return \false;
    }
}
