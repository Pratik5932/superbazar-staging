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


namespace Mirasvit\RewardsCheckout\Plugin\ThirdParty;

use /** @noinspection PhpUndefinedNamespaceInspection */
    Amasty\CommonRules\Model\Modifiers\ModifierInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;

/**
 * Apply rewards discount to Amasty shipping restriction rate calculations
 *
 * @package Mirasvit\Rewards\Plugin
 */
class AmastyCommonRulesAddRewardsDiscount
{
    /**
     * @var PurchaseHelper
     */
    private $purchaseHelper;

    public function __construct(
        PurchaseHelper $purchaseHelper
    ) {
        $this->purchaseHelper = $purchaseHelper;
    }

    /**
     * @param ModifierInterface $amastyModifier
     * @param \callable         $proceed
     * @param DataObject        $object
     * @return DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundModify(ModifierInterface $amastyModifier, $proceed, $object)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $returnValue = $proceed($object);

        if (!($returnValue instanceof Address)) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

            return $returnValue;
        }

        $purchase = $this->purchaseHelper->getByQuote($returnValue->getQuote());

        if (!$purchase) {
            \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
            return $returnValue;
        }

        $returnValue->setSubtotal($returnValue->getSubtotal() - $purchase->getSpendAmount());
        $returnValue->setBaseSubtotal($returnValue->getBaseSubtotal() - $purchase->getBaseSpendAmount());
        $returnValue->setPackageValueWithDiscount($returnValue->getBaseSubtotal() - $purchase->getBaseSpendAmount());

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

        return $returnValue;
    }
}