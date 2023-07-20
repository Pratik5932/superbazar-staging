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



namespace Mirasvit\RewardsCustomerAccount\Block\Order\Creditmemo;

use Mirasvit\Rewards\Helper\Data as RewardsData;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Rewards\Model\ResourceModel\Refund\CollectionFactory;

class Totals extends \Magento\Framework\View\Element\Template
{
    protected $rewardsData;

    protected $context;

    private   $refundCollectionFactory;

    public function __construct(
        RewardsData $rewardsData,
        CollectionFactory $refundCollectionFactory,
        Context $context,
        array $data = []
    ) {
        $this->rewardsData             = $rewardsData;
        $this->refundCollectionFactory = $refundCollectionFactory;
        $this->context                 = $context;
        parent::__construct($context, $data);
    }


    public function initTotals()
    {
        /** @var mixed $parent */
        $parent           = $this->getParentBlock();
        $refundCollection = $this->refundCollectionFactory->create()
        ->addFieldToFilter('creditmemo_id', $parent->getSource()->getId());
        $refund = $refundCollection->getFirstItem();

        if ($refund && $refund->getBaseRefunded()) {
            $parent->addTotal(new \Magento\Framework\DataObject([
                'code'       => 'rewards_refunded_amount',
                'value'      => $refund->getBaseRefunded(),
                'base_value' => $refund->getRefunded(),
                'label'      => __(
                    '%1 Amount Refunded', $this->rewardsData->getPointsName()),
                'area'       => 'footer',
                'strong'     => $this->getStrong(),
            ]), 'refunded');
        }

        return $this;
    }
}
