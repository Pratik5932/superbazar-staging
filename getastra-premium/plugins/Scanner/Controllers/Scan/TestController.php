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
 * @date   2019-03-19
 */
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Controllers\Scan;

use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\OptionsHelper;
use AstraPrefixed\GetAstra\Plugins\Scanner\Services\ScanEngine;
//use Interop\Container\ContainerInterface;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Slim\Http\Request;
use AstraPrefixed\Slim\Http\Response;
use AstraPrefixed\GetAstra\Plugins\Scanner\Helpers\StatusHelper;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
class TestController
{
    protected $container;
    private $commonHelper;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->commonHelper = new CommonHelper();
    }
    public static function hex2bin($hexString, $strictPadding = \false)
    {
        /* Type checks: */
        if (!\is_string($hexString)) {
            throw new TypeError('Argument 1 must be a string, ' . \gettype($hexString) . ' given.');
        }
        /** @var int $hex_pos */
        $hex_pos = 0;
        /** @var string $bin */
        $bin = '';
        /** @var int $c_acc */
        $c_acc = 0;
        /** @var int $hex_len */
        $hex_len = self::strlen($hexString);
        /** @var int $state */
        $state = 0;
        if (($hex_len & 1) !== 0) {
            if ($strictPadding) {
                throw new RangeException('Expected an even number of hexadecimal characters');
            } else {
                $hexString = '0' . $hexString;
                ++$hex_len;
            }
        }
        $chunk = \unpack('C*', $hexString);
        while ($hex_pos < $hex_len) {
            ++$hex_pos;
            /** @var int $c */
            $c = $chunk[$hex_pos];
            /** @var int $c_num */
            $c_num = $c ^ 48;
            /** @var int $c_num0 */
            $c_num0 = $c_num - 10 >> 8;
            /** @var int $c_alpha */
            $c_alpha = ($c & ~32) - 55;
            /** @var int $c_alpha0 */
            $c_alpha0 = ($c_alpha - 10 ^ $c_alpha - 16) >> 8;
            if (($c_num0 | $c_alpha0) === 0) {
                throw new RangeException('hex2bin() only expects hexadecimal characters');
            }
            /** @var int $c_val */
            $c_val = $c_num0 & $c_num | $c_alpha & $c_alpha0;
            if (0 === $state) {
                $c_acc = $c_val * 16;
            } else {
                $bin .= \pack('C', $c_acc | $c_val);
            }
            $state ^= 1;
        }
        return $bin;
    }
}
