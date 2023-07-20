<?php
namespace Mexbs\AdditionalPromotions\Model\Rewrite\Rule\Condition;

use Magento\Framework\App\ObjectManager;

class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    private $apHelper;

    protected function _getApHelper(){
        if(!$this->apHelper){
            $this->apHelper = ObjectManager::getInstance()->get("Mexbs\\AdditionalPromotions\\Helper\\Data");
        }
        return $this->apHelper;
    }

    public function getSupportsCartHints(){
        return true;
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $this->setLastCartHintData(null);
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod()) {
            $address->setPaymentMethod($model->getQuote()->getPayment()->getMethod());
        }

        $validationResult =  \Magento\Rule\Model\Condition\AbstractCondition::validate($address);
        $attr = $this->getAttribute();

        if(in_array($attr, ['base_subtotal', 'total_qty', 'weight'])){
            $cartHintData = array_merge([
                'volume_type' => $attr,
                'hint_singular' => null,
                'hint_plural' => null
            ], $this->_getApHelper()->getCartHintAddData($this->getOperator(), $this->getValue(), $address->getData($attr)));

            $this->setLastCartHintData($cartHintData);
        }

        return $validationResult;
    }
}