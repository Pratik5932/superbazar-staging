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
define([
    'jquery',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'Bss_OrderDeliveryDate/js/model/delivery-form-data'
], function (
    $,
    setShippingInformationAction,
    stepNavigator,
    quote,
    checkoutDataResolver,
    checkoutData,
    registry,
    deliveryFormData
) {
    'use strict';

    return function (Component) {
        return Component.extend({
            setShippingInformation: function () {
                if (this.validateShippingInformation()) {
                    quote.billingAddress(null);
                    checkoutDataResolver.resolveBillingAddress();
                    registry.async('checkoutProvider')(function (checkoutProvider) {
                        var shippingAddressData = checkoutData.getShippingAddressFromData();

                        if (shippingAddressData) {
                            checkoutProvider.set(
                                'shippingAddress',
                                $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                            );
                        }
                    });
                    setShippingInformationAction().done(
                        function () {
                            // Add delivery data to storange
                            // Mixin version M2.3.4
                            var data = $('.bss-delivery'),
                                dataPost = data.find(':input[name="shipping_arrival_date"], select[name="delivery_time_slot"], textarea[name="shipping_arrival_comments"]').serializeArray();
                            deliveryFormData.setSelectedDeliveryData(dataPost);
                            // End
                            stepNavigator.next();
                        }
                    );
                }
            }
        });
    };
});