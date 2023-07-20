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



namespace Mirasvit\RewardsCatalog\Service;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\CacheInterface;

class CacheService
{
    const REWARDS_PRODUCT_CACHE = 'RewardsProductCache';

    private $serializer;

    private $cache;

    public function __construct(
        Json $serializer,
        CacheInterface $cache
    ) {
        $this->serializer = $serializer;
        $this->cache      = $cache;
    }
    /**
     * @param string $instance
     * @param array  $dataKey
     *
     * @return string
     */
    private function getCacheKey($instance, $dataKey)
    {
        return mb_strtoupper('mst_' . $instance . '_' . implode('_', $dataKey));
    }

    /**
     * @param string $instance
     * @param array  $dataKey
     *
     * @return mixed
     */
    public function getCache($instance, $dataKey)
    {
        $cachedData = $this->cache->load($this->getCacheKey($instance, $dataKey));
        if (empty($cachedData)) {
            return null;
        } else {
            $cachedData = $this->serializer->unserialize($cachedData);
            $cachedData = array_values($cachedData)[0];
        }

        return is_array($cachedData) ? $cachedData : [$cachedData];
    }

    /**
     * @param string $instance
     * @param array  $dataKey
     * @param array  $dataValue
     *
     * @return mixed
     */
    public function setCache($instance, $dataKey, $dataValue)
    {
        $this->cache->save($this->serializer->serialize($dataValue), $this->getCacheKey($instance, $dataKey));
    }
}
