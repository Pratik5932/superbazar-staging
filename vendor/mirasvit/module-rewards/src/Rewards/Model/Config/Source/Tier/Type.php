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



namespace Mirasvit\Rewards\Model\Config\Source\Tier;

use Mirasvit\Rewards\Api\Data\TierInterface;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
        return [
            [
                'label' => __('sum of earned points'),
                'value' => TierInterface::TYPE_POINT,
            ],
            [
                'label' => __('sum of spent %1', $currencyCode),
                'value' => TierInterface::TYPE_ORDER,
            ],
        ];
    }
}
