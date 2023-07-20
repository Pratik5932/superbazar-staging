/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/checkout-data',
    ],
    function ($, quote, urlBuilder, storage, errorProcessor, customer, fullScreenLoader, checkoutData) {
        'use strict';
        return function (messageContainer) {
            var validatedEmail = checkoutData.getValidatedEmailValue();
            var serviceUrl,
                payload,
                method = 'put',
                paymentData = {method: quote.paymentMethod().method, po_number: null, additional_data: null};

            if (validatedEmail && !customer.isLoggedIn()) {
                quote.guestEmail = validatedEmail;
            }

            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl(
                    '/guest-carts/:cartId/set-payment-information',
                    {
                        cartId: quote.getQuoteId()
                    }
                );
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData
                };
                method = 'post';
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/selected-payment-method', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    method: paymentData
                };
            }
            fullScreenLoader.startLoader();

            return storage[method](
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    errorProcessor.process(response, this.messageContainer);
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);
