<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType('Magento\SalesRule\Model\Rule\Condition\Product\Combine');
    }

    public function getNewChildSelectOptions()
    {
        $conditions = [
            [
                'label' => __('Order status'),
                'value' => 'Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order\OrderStatus'
            ],
            [
                'label' => __('Days passed since the order was placed'),
                'value' => 'Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order\OrderDaysSincePlaced'
            ],
            [
                'label' => __('Conditions Combination'),
                'value' => 'Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order\Combine'
            ]
        ];

        return $conditions;
    }

    public function getValueElement()
    {
        $valueElement = parent::getValueElement();
        $valueElement->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
        return $valueElement;
    }

    public function getAggregatorElement()
    {
        $element = parent::getAggregatorElement();
        $element->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
        return $element;
    }
}
