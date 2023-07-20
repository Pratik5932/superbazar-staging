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


namespace Mirasvit\Rewards\Model\Earning\Rule\Condition;

use Magento\Framework\App\ProductMetadataInterface;

class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    private $paymentConfig;

    private $scopeConfig;

    private $productMetadata;

    private $taxConfig;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        array $data = []
    ) {
        parent::__construct(
            $context, $directoryCountry, $directoryAllregion, $shippingAllmethods, $paymentAllmethods, $data
        );

        $this->productMetadata = $productMetadata;
        $this->paymentConfig   = $paymentConfig;
        $this->scopeConfig     = $scopeConfig;
        $this->taxConfig       = $taxConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $attributes = $this->getAttributeOption();
        $attributes['payment_method'] = __('Payment Method');
        $attributes['payment_method_additional'] = __('Additional Payment Method');

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'payment_method':
                return 'select';
        }

        return parent::getInputType();
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'payment_method':
                return 'select';
        }
        return parent::getValueElementType();
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getValueSelectOptions()
    {
        if (version_compare($this->productMetadata->getVersion(), "2.3.0", "<")) {

            if (!$this->hasData('value_select_options')) {
                switch ($this->getAttribute()) {
                    case 'payment_method':
                        $options = [];
                        $groups  = $this->paymentConfig->getGroups();
                        foreach ($groups as $code => $title) {
                            $options[$code] = ['value' => [], 'label' => $title];
                        }

                        $methodList = $this->scopeConfig->getValue('payment');
                        foreach ($methodList as $code => $data) {
                            if (!empty($data['group']) && !empty($data['title'])) {
                                $options[$data['group']]['value'][$code] = ['value' => $code, 'label' => $data['title']];
                            }
                        }
                        foreach ($options as $k => $option) {
                            if (empty($option['value'])) {
                                unset($options[$k]);
                            }
                        }
                        $this->setData('value_select_options', $options);
                        break;
                }
            }
        }

        return parent::getValueSelectOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $model;

        if (
            'base_subtotal' == $this->getAttribute() &&
            $this->taxConfig->displayCartSubtotalInclTax($address->getQuote()->getStore())
        ) {
            $this->setAttribute('subtotal_incl_tax');
        }

        if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod() ) {
            $address->setPaymentMethod($model->getQuote()->getPayment()->getMethod());
        }

        if ('payment_method_additional' == $this->getAttribute() && !$address->hasPaymentMethodAdditional()) {
            $method = $model->getQuote()->getPayment()->getMethod();
            if (!$method) {
                $method = $address->getPaymentMethod();
            }
            $address->setPaymentMethodAdditional($method);
        }

        return parent::validate($address);
    }
}
