/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm'
], function (ko, $, quote, urlManager, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction,
    totals, fullScreenLoader, alert, confirmation
) {
    'use strict';

    var dataModifiers = [],
        successCallbacks = [],
        failCallbacks = [],
        action;

    /**
     * Apply provided coupon.
     *
     * @param {String} couponCode
     * @param {Boolean}isApplied
     * @returns {Deferred}
     */
    action = function (couponCode, isApplied) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getApplyCouponUrl(couponCode, quoteId),
            message = $t('Your coupon was successfully applied.'),
            data = {},
            headers = {};

        //Allowing to modify coupon-apply request
        dataModifiers.forEach(function (modifier) {
            modifier(headers, data);
        });
        fullScreenLoader.startLoader();

        return storage.put(
            url,
            data,
            false,
            null,
            headers
        ).done(function (response) {
            var deferred;

            if (response) {
                deferred = $.Deferred();

                isApplied(true);
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(true);
                });
                messageContainer.addSuccessMessage({
                    'message': response
                });

                confirmation({
                    //title: $.mage.__('Some title'),
                    content: 'Your coupon was successfully applied. You will be redirected to previous page to calculate shipping charges.',
                    buttons: [{
                        text: $.mage.__('OK'),
                        class: 'action accept',

                        /**
                         * Click handler.
                         */
                        click: function () {
                            this.closeModal(true);
                            location.href ="/checkout";
                        }
                    }],
                    closed: function () {
                        //do the magic here
                        location.href ="/checkout";
                    }
                    

                });

                
                //Allowing to tap into apply-coupon process.
                successCallbacks.forEach(function (callback) {
                    callback(true);
                });
                
                // setTimeout(function() {
                //  location.href ="/checkout";
                // }, 5500);
                //location.href ="/checkout/#payment";
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(true);
            errorProcessor.process(response, messageContainer);
            //Allowing to tap into apply-coupon process.
            failCallbacks.forEach(function (callback) {
                callback(response);
            });
            //location.reload();
        });
    };

    /**
     * Modifying data to be sent.
     *
     * @param {Function} modifier
     */
    action.registerDataModifier = function (modifier) {
        dataModifiers.push(modifier);
    };

    /**
     * When successfully added a coupon.
     *
     * @param {Function} callback
     */
    action.registerSuccessCallback = function (callback) {
        successCallbacks.push(callback);
    };

    /**
     * When failed to add a coupon.
     *
     * @param {Function} callback
     */
    action.registerFailCallback = function (callback) {
        failCallbacks.push(callback);
    };

    return action;
});
