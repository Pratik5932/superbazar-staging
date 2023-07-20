<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order;

class OrderDaysSincePlaced extends \Magento\Rule\Model\Condition\AbstractCondition{
    protected $apHelper;
    protected $orderStatusesOptionArray;
    protected $dateTime;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->apHelper = $apHelper;
        $this->dateTime = $dateTime;
    }

    public function loadAttributeOptions()
    {
        $attributes = [
            'days_since_placed' => __('Days since the order was placed')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getValueElement(){
        $element = parent::getValueElement();
        $element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));
        return $element;
    }

    public function getOperatorElement()
    {
        $element = parent::getOperatorElement();
        $element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));
        return $element;
    }

    public function getInputType()
    {
        return 'numeric';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $orderCreatedAtDate = $model->getCreatedAt();
        if(!$orderCreatedAtDate){
            return false;
        }
        $orderCreatedAtTimestamp = strtotime($orderCreatedAtDate);
        if(!$orderCreatedAtTimestamp){
            return false;
        }

        $currentDateTimestamp = $this->dateTime->gmtTimestamp();
        $differenceInDays = floor(($currentDateTimestamp-$orderCreatedAtTimestamp)/60/60/24);

        if($differenceInDays < 0){
            return false;
        }

        return $this->validateAttribute($differenceInDays);
    }
}