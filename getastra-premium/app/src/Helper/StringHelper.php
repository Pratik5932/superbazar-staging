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
 * @date   2019-04-05
 */
namespace AstraPrefixed\GetAstra\Client\Helper;

class StringHelper
{
    public static function getStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = \strpos($string, $start);
        if (0 == $ini) {
            return '';
        }
        $ini += \strlen($start);
        $len = \strpos($string, $end, $ini) - $ini;
        return \substr($string, $ini, $len);
    }
    public static function truncate($message, $length = 255, $suffix = '...')
    {
        return \strlen($message) > $length ? \substr($message, 0, $length - \strlen($suffix)) . $suffix : $message;
    }
}
