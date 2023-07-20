<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\CustomerSales;

class LifetimePaidAmount extends \Magento\Rule\Model\Condition\AbstractCondition{
    protected $orderCollectionFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function loadAttributeOptions()
    {
        $attributes = [
            'customer_lifetime_paid_amount' => __('Lifetime paid amount')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        return 'string';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        if (!($address instanceof \Magento\Quote\Model\Quote\Address)) {
            return false;
        }
        /**
         * @var \Magento\Quote\Model\Quote\Address $address
         */
        $quote = $address->getQuote();
        $customerId = $quote->getCustomerId();
        if(!$customerId){
            return false;
        }

        /**
         * @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
         */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addAttributeToFilter("customer_id", $customerId);

        $totalPaid = 0;
        foreach($orderCollection as $order){
            $totalPaid += ($order->getTotalPaid() - $order->getTotalRefunded());
        }

        return $this->validateAttribute($totalPaid);
    }
}