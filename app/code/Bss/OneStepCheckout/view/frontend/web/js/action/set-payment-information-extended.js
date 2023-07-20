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
 * @category  BSS
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'underscore',
    'Magento_Checkout/js/model/payment/place-order-hooks',
    'mage/url'
], function (quote, urlBuilder, storage, errorProcessor, customer, getTotalsAction, fullScreenLoader, _, hooks, url) {
    'use strict';

    /**
     * Filter template data.
     *
     * @param {Object|Array} data
     */
    var filterTemplateData = function (data) {
        return _.each(data, function (value, key, list) {
            if (_.isArray(value) || _.isObject(value)) {
                list[key] = filterTemplateData(value);
            }

            if (key === '__disableTmpl' || key === 'title') {
                delete list[key];
            }
        });
    };

    return function (messageContainer, paymentData, skipBilling) {
        var serviceUrl,
            payload,
            headers = {};

        paymentData = filterTemplateData(paymentData);
        skipBilling = skipBilling || false;
        payload = {
            cartId: quote.getQuoteId(),
            paymentMethod: paymentData
        };

        /**
         * Checkout for guest and registered customer.
         */
        if (!customer.isLoggedIn()) {
            if (quote.guestEmail){
                serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/set-payment-information', {
                    cartId: quote.getQuoteId()
                });
                payload.email = quote.guestEmail;
            }
        } else {
            var listAddress = customer.customerData.addresses;
            if (listAddress && listAddress.length > 0){
                serviceUrl = urlBuilder.createUrl('/carts/mine/set-payment-information', {});
            }
        }

        if (skipBilling === false) {
            payload.billingAddress = quote.billingAddress();
        }

        if (serviceUrl){
            fullScreenLoader.startLoader();
            _.each(hooks.requestModifiers, function (modifier) {
                modifier(headers, payload);
            });

            return storage.post(
                serviceUrl, JSON.stringify(payload), true, 'application/json', headers
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                    _.each(hooks.afterRequestListeners, function (listener) {
                        listener();
                    });
                }
            );
        } else {
            fullScreenLoader.stopLoader();
        }
    };
});
