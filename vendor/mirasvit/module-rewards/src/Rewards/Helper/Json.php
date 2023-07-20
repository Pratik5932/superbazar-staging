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

class Json
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $jsonApiObject = null;

    public function __construct()
    {
        if (interface_exists(\Magento\Framework\Serialize\SerializerInterface::class, false)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->jsonApiObject = $objectManager->get('\Magento\Framework\Serialize\SerializerInterface');
        }
    }

    /**
     * @param string|array $data
     * throw InvalidArgumentException
     *
     * @return string
     */
    public function serialize($data)
    {
        if ($this->jsonApiObject) {
            return $this->jsonApiObject->serialize($data);
        }

        $result = json_encode($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }

    /**
     * @param string $string
     *
     * @return string|array
     */
    public function unserialize($string)
    {
        if ($this->jsonApiObject) {
            try {
                return $this->jsonApiObject->unserialize($string);
            } catch (\Exception $e) {
                return [0 => $string];
            }
        }

        $result = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->isSerialized($string)) {
                try {
                    return unserialize($string);
                } catch (\Exception $e) {
                    return [0 => $string];
                }
            }
            if (strpos($result, '{') === 0 || strpos($result, '[') === 0) {
                throw new \InvalidArgumentException('Unable to unserialize value.');
            } else {
                return [0 => $string];
            }
        }
        return $result;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function isSerialized($string)
    {
        return strpos($string, 'a:') === 0;
    }

    /**
     * @param string|array|int $data
     *
     * @return bool
     */
    public function isEncoded($data)
    {
        try {
            $result = $this->unserialize($data);
            if ($result && is_string($data) &&
                (strpos($data, '{') !== 0 || (strpos($data, '{') === 0 && strpos($data, '{', 1) === 1)) &&
                strpos($data, '[') !== 0
            ) {
                $result = false;
            }
        } catch (\InvalidArgumentException $e) {
            $result = false;
        }
        if ($result == $data) {
            $result = false;
        }

        return $result;
    }
}
