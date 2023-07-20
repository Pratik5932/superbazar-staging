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


namespace Mirasvit\Rewards\Model\Spending\Rule\Condition;

use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Rule\Model\Condition\Context;
use Magento\Directory\Model\Config\Source\Country as DirectoryCountry;
use Magento\Directory\Model\Config\Source\Allregion as DirectoryAllregion;
use Magento\Shipping\Model\Config\Source\Allmethods as ShippingAllmethods;
use Magento\Payment\Model\Config\Source\Allmethods as PaymentAllmethods;

class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    const OPTION_COUPON_USED   = 'coupon_used';
    const OPTION_COUPON_CODE   = 'coupon_code';
    const OPTION_DISCOUNT_USED = 'discount_code';

    private $taxConfig;

    private $catalogRule;

    protected $dateTime;

    protected $session;

    public function __construct(
        CatalogRule $catalogRule,
        DateTime $dateTime,
        Session $session,
        TaxConfig $taxConfig,
        Context $context,
        DirectoryCountry $directoryCountry,
        DirectoryAllregion $directoryAllregion,
        ShippingAllmethods $shippingAllmethods,
        PaymentAllmethods $paymentAllmethods,
        array $data = []
    ) {
        parent::__construct(
            $context, $directoryCountry, $directoryAllregion, $shippingAllmethods, $paymentAllmethods, $data
        );

        $this->catalogRule = $catalogRule;
        $this->taxConfig   = $taxConfig;
        $this->dateTime    = $dateTime;
        $this->session     = $session;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();
        $attributes = $this->getAttributeOption();
        $attributes[self::OPTION_COUPON_USED]   = __('Coupon Used');
        $attributes[self::OPTION_COUPON_CODE]   = __('Coupon Code');
        $attributes[self::OPTION_DISCOUNT_USED] = __('Discount Used');

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case self::OPTION_COUPON_USED:
            case self::OPTION_DISCOUNT_USED:
                return 'select';
        }
        return parent::getInputType();
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case self::OPTION_COUPON_USED:
            case self::OPTION_DISCOUNT_USED:
                return 'select';
        }
        return parent::getValueElementType();
    }

    /**
     * {@inheritdoc}
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case self::OPTION_COUPON_USED:
                case self::OPTION_DISCOUNT_USED:
                    $options = [
                        ['value' => 0, 'label' => __('No')],
                        ['value' => 1, 'label' => __('Yes')],
                    ];
                    break;
                default:
                    $options = [];
            }
            if (!$options) {
                $options = parent::getValueSelectOptions();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
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

        if (
            'base_subtotal' == $this->getAttribute() &&
            $this->taxConfig->displayCartSubtotalInclTax($address->getQuote()->getStore())
        ) {
            $this->setAttribute('subtotal_incl_tax');
        }

        $appliedRules = $address->getQuote()->getAppliedRuleIds();
        $appliedCatalogRules = false;

        foreach ($address->getQuote()->getAllVisibleItems() as $item) {
            $appliedCatalogRules = $this->getRulesFromProduct(
              $this->dateTime->gmtDate(),
              $address->getQuote()->getStore()->getWebsiteId(),
              $this->session->getCustomer()->getGroupId(),
              $item->getProductId());

              if ($appliedCatalogRules) {
                break;
              }
        }

        $discountUsed = 0;

        if ($appliedRules) {
            $address->setData(self::OPTION_COUPON_USED, (int)!empty($address->getQuote()->getCouponCode()));
            $address->setData(self::OPTION_COUPON_CODE, $address->getQuote()->getCouponCode());
            if ($appliedRules &&
                (abs($address->getBaseDiscountAmount()) > 0 || abs($address->getBaseShippingDiscountAmount()) > 0)
            ) {
                $discountUsed = 1;
            }
        } else {
            $address->setData(self::OPTION_COUPON_USED, 0);
        }

        if ($appliedCatalogRules) {
            $discountUsed = 1;
        }

        $address->setData(self::OPTION_DISCOUNT_USED, $discountUsed);

        return parent::validate($address);
    }

    /**
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return bool
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
       return (bool) count($this->catalogRule->getRulesFromProduct($date, $websiteId, $customerGroupId, $productId));
    }
}
