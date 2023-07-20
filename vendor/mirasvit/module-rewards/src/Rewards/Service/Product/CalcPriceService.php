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



namespace Mirasvit\Rewards\Service\Product;

use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class CalcPriceService
{
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var TimezoneInterface
     */
    private $dateTime;
    /**
     * @var CatalogRule
     */
    private $ruleResource;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CatalogRule $ruleResource,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        TimezoneInterface $dateTime
    ) {
        $this->customerSession = $customerSession;
        $this->dateTime        = $dateTime;
        $this->ruleResource    = $ruleResource;
        $this->storeManager    = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Api\Data\ProductInterface $product
     * @return float
     */
    public function getBaseCatalogPriceRulePrice($product)
    {
        $price = 0;
        if ($product->getTypeId() == 'giftcard') {
            return $price;
        }
        try {
            $catalogPriceObject = $product->getPriceInfo()->getPrice('catalog_rule_price');
        } catch (\Exception $e) {
            return 0;
        }
        if ($catalogPriceObject && $catalogPriceObject->getValue()) {
            $price = $this->ruleResource->getRulePrice(
                $this->dateTime->scopeDate($this->storeManager->getStore()->getId()),
                $this->storeManager->getStore()->getWebsiteId(),
                $this->customerSession->getCustomerGroupId(),
                $product->getId()
            );
        }

        return $price;
    }
}