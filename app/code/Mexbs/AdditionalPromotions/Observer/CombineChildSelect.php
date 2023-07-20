<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class CombineChildSelect implements ObserverInterface{
    protected $customerAttributeCondition;

    public function __construct(
        \Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Customer\CustomerAttribute $customerAttributeCondition
    ) {
        $this->customerAttributeCondition = $customerAttributeCondition;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $additional = $observer->getEvent()->getAdditional();
        $additionalConditions = $additional->getConditions();

        $customerSalesHistoryConditions = [
            'label' => __('Customer Sales History'),
            'value' => [
                [
                    'value' => 'Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\CustomerSales\CompletedOrderCount',
                    'label' => "Completed Orders Count"
                ],
                [
                    'value' => 'Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\CustomerSales\LifetimePaidAmount',
                    'label' => "Lifetime Paid Amount"
                ]
            ]
        ];
        $additionalConditions[] = $customerSalesHistoryConditions;


        $customerAttributes = $this->customerAttributeCondition->loadAttributeOptions()->getAttributeOption();
        $cAttributesFormatted = [];
        foreach ($customerAttributes as $code => $label) {
            $cAttributesFormatted[] = [
                'value' => 'Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Customer\CustomerAttribute|' . $code,
                'label' => $label,
            ];
        }


        $customerAttributesConditions = [
            'label' => __('Customer Attribute'),
            'value' => $cAttributesFormatted
        ];
        $additionalConditions[] = $customerAttributesConditions;

        $orderSubselectionConditions = [
            'label' => __('Customer Orders Subselection'),
            'value' => 'Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Order\Subselect'
        ];
        $additionalConditions[] = $orderSubselectionConditions;

        $additional->setConditions($additionalConditions);
    }
}