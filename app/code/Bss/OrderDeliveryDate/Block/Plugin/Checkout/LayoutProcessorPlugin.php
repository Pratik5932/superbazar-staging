<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderDeliveryDate\Block\Plugin\Checkout;

class LayoutProcessorPlugin
{
    const DELIVERY_FORM_DISPLAY_AT_SHIPPING_ADDRESS = 0;
    const DELIVERY_FORM_DISPLAY_AT_SHIPPING_METHOD = 1;
    const DELIVERY_FORM_DISPLAY_AT_REVIEW_PAYMENTS = 2;

    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * LayoutProcessorPlugin constructor.
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Bss\OrderDeliveryDate\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->helper = $helper;
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $container = null;
        $check = false;
        $cartItems = $this->cart->getQuote()->getAllVisibleItems();
        foreach ($cartItems as $cartItem) {
            $productType = $cartItem->getProduct()->getTypeId();
            if ($productType != "downloadable" && $productType != "virtual") {
                $check = true;
                continue;
            }
        }

        if (!$this->helper->isEnabled() || !$check) {
            return $jsLayout;
        }
        if ($this->helper->getDisplayAt() == self::DELIVERY_FORM_DISPLAY_AT_SHIPPING_ADDRESS) {
            $container = 'before-form';
        } elseif ($this->helper->getDisplayAt() == self::DELIVERY_FORM_DISPLAY_AT_SHIPPING_METHOD) {
            $container = 'before-shipping-method-form';
        }

        // before place order
        if ($this->helper->getDisplayAt() == self::DELIVERY_FORM_DISPLAY_AT_REVIEW_PAYMENTS) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['beforeMethods']['children']['delivery-date'] = [
                                    'component' => 'Bss_OrderDeliveryDate/js/view/delivery-date',
                                    'displayArea' => 'delivery-date',
                                    'sortOrder' => 11
                                ];
        } else {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children'][$container]['children']['delivery-date'] = [];
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children'][$container]['children']['delivery-date']
            ['component'] = 'Bss_OrderDeliveryDate/js/view/delivery-date';
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children'][$container]['children']['delivery-date']['sortOrder'] = 10;
        }

        return $jsLayout;
    }
}
