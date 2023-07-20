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



namespace Mirasvit\RewardsAdminUi\Plugin\Customer\Component\DataProvider\Document;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Api\AttributeValueFactory;
use Mirasvit\Rewards\Helper\Balance;

/**
 * Set rewards balance value to customer export document
 */
class CustomAttributePlugin
{
    private $attributeValueFactory;
    private $balanceHelper;

    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        Balance $balanceHelper
    ) {
        $this->attributeValueFactory = $attributeValueFactory;
        $this->balanceHelper         = $balanceHelper;
    }

    /**
     * @param Document $subject
     * @param \Closure $proceed
     * @param string   $code
     *
     * @return \Magento\Framework\Api\AttributeInterface|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCustomAttribute(Document $subject, \Closure $proceed, $code)
    {
        if ($code == 'mst_rewards_balance') {
            /** @var \Magento\Framework\Api\AttributeInterface $attributeValue */
            $attributeValue = $this->attributeValueFactory->create();
            $attributeValue->setAttributeCode($code);
            $attributeValue->setValue($this->balanceHelper->getBalancePoints($subject->getId()));
            return $attributeValue;
        } else {
            $result = $proceed($code);
        }

        return $result;
    }
}
