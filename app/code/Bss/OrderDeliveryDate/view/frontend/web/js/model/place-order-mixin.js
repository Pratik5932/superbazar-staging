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
    'mage/utils/wrapper',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Customer/js/customer-data'
], function ($, wrapper, storage, errorProcessor, fullScreenLoader, customerData) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, serviceUrl, payload, messageContainer) {
            fullScreenLoader.startLoader();
            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            ).done(
                function (response) {
                    var clearData = {
                        'selectedShippingAddress': null,
                        'shippingAddressFromData': null,
                        'newCustomerShippingAddress': null,
                        'selectedShippingRate': null,
                        'selectedPaymentMethod': null,
                        'selectedBillingAddress': null,
                        'billingAddressFromData': null,
                        'newCustomerBillingAddress': null
                    };

                    // Add clear delivery data
                    // Mixin M2.3.4
                    var clearDeliveryData = {
                        'selectedDeliveryData': null
                    }

                    if (response.responseType !== 'error') {
                        customerData.set('checkout-data', clearData);

                        // Clear delivery data
                        // Mixin M2.3.4
                        customerData.set('delivery-data', clearDeliveryData);
                    }
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        });
    };
});