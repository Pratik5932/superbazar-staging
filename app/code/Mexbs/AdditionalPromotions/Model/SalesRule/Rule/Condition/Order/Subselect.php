<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order;

class Subselect extends Combine
{
    protected $customerSession;
    protected $orderCollectionFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;

        $this->setType('Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order\Subselect')
            ->setValue(null);
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([
            'number_of_created_orders' => __('number of created orders'),
            'average_order_grandtotal' => __('average order grandtotal'),
            'sum_grandtotal_of_orders' => __('sum of grandtotals of orders')
        ]);
        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );
        return $this;
    }

    public function getValueElement(){
        $element = parent::getValueElement();
        $element->setRenderer($this->_layout->getBlockSingleton('Magento\Rule\Block\Editable'));
        return $element;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
            "If %1 %2 %3 for a subselection of orders matching %4 of these conditions:",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getAggregatorElement()->getHtml()
        );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if (!$this->getConditions()) {
            return false;
        }
        $addressCustomerId = $model->getCustomerId();
        if(!$addressCustomerId){
            return false;
        }

        $loggedInCustomerId = $this->customerSession->getCustomerId();
        if(!$loggedInCustomerId
            || ($addressCustomerId != $loggedInCustomerId)){
            return false;
        }

        $customerOrdersCollection = $this->orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $addressCustomerId);

        $attr = $this->getAttribute();

        $sumGrandtotalOfOrders = 0;
        $numberOfCreatedOrders = 0;

        foreach ($customerOrdersCollection as $order) {
            if (parent::validate($order)) {
                $sumGrandtotalOfOrders += $order->getGrandTotal();
                $numberOfCreatedOrders++;
            }
        }

        if($numberOfCreatedOrders == 0){
            $averageOrderGrandtotal = 0;
        }else{
            $averageOrderGrandtotal = $sumGrandtotalOfOrders/$numberOfCreatedOrders;
        }

        switch($attr){
            case 'number_of_created_orders':
                return $this->validateAttribute($numberOfCreatedOrders);
            case 'average_order_grandtotal':
                return $this->validateAttribute($averageOrderGrandtotal);
            case 'sum_grandtotal_of_orders':
                return $this->validateAttribute($sumGrandtotalOfOrders);
            break;
        }
        return false;
    }
}
