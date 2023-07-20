<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rewards\Helper;

use Mirasvit\Core\Service\CompatibilityService;

class Serializer
{
    private $serializer;

    private $jsoner;

    public function __construct()
    {
        if (CompatibilityService::is21() || CompatibilityService::is20()) {
            $this->serializer = \Zend\Serializer\Serializer::factory('PhpSerialize');
            $this->jsoner = \Zend\Serializer\Serializer::factory('Json');
        } else {
            /** @var \Magento\Framework\Serialize\Serializer\Serialize $serializer */
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\Serializer\Serialize::class
            );
            $this->jsoner = CompatibilityService::getObjectManager()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Serialize|mixed
     */
    public function getSerialiser()
    {
        return $this->serializer;
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Json|mixed
     */
    public function getJsoner()
    {
        return $this->jsoner;
    }

    /**
     * @param array|string $data
     * @return array|bool|string
     */
    public function serialize($data)
    {
        $serialized = true;
        $result = [];
        try {
            $result = $this->jsoner->serialize($data);
        } catch(\Exception $e) {
            $serialized = false;
        }
        if (!$serialized) {
            try {
                $result = $this->serializer->serialize($data);
            } catch (\Exception $e) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param string $string
     * @return array
     * @throws \Zend_Json_Exception
     */
    public function unserialize($string)
    {
        $unserialized = true;

        try {
            new \ReflectionClass('Zend\Json\Json');
        } catch (\Exception $e) {}

        $useDecoder = false;
        $result = false;
        // we use this because json_decode does not work correct for php5
        if (class_exists('Zend\Json\Json', false)) {
            $useDecoder = \Zend\Json\Json::$useBuiltinEncoderDecoder;
            \Zend\Json\Json::$useBuiltinEncoderDecoder = true;
        }
        try {
            $result = $this->jsoner->unserialize($string);
        } catch(\Exception $e) {
            $unserialized = false;
        }
        if (!$unserialized) {
            try {
                $result = $this->serializer->unserialize($string);
            } catch (\Exception $e) {
                $result = $string;
            }
        }
        if (class_exists('Zend\Json\Json', false)) {
            \Zend\Json\Json::$useBuiltinEncoderDecoder = $useDecoder;
        }

        return is_array($result) ? $result : [0 => $result];
    }
}
