<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order;

class OrderStatus extends \Magento\Rule\Model\Condition\AbstractCondition{
    protected $apHelper;
    protected $orderStatusesOptionArray;
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->apHelper = $apHelper;
    }

    public function loadAttributeOptions()
    {
        $attributes = [
            'status' => __('Order status')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            if(!$this->orderStatusesOptionArray){
                $this->orderStatusesOptionArray = $this->apHelper->getOrderStatusesOptionArray();
            }
            $this->setData('value_select_options', $this->orderStatusesOptionArray);
        }
        return $this->getData('value_select_options');
    }

    public function getOperatorElement()
    {
        $element = parent::getOperatorElement();
        $element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));
        return $element;
    }

    public function getValueElement(){
        $element = parent::getValueElement();
        $element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));
        return $element;
    }

    public function getInputType()
    {
        return 'select';
    }

    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $validatedValue = $model->getStatus();
        return $this->validateAttribute($validatedValue);
    }
}