define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery',
        'Magento_Checkout/js/action/get-totals',
    ],
    function (quote, fullScreenLoader, jQuery, getTotalsAction) {
        'use strict';
        return function (paymentMethod) {
            quote.paymentMethod(paymentMethod);

            var url = window.paymentFeeConfig.applyMethodUrl;
            if (window.paymentFeeConfig.isEnabled && url) {
                fullScreenLoader.startLoader();

                jQuery.ajax(url, {
                    data: { payment_method: paymentMethod.method },
                    method: 'POST',
                    complete: function () {
                        getTotalsAction([]);
                        fullScreenLoader.stopLoader();
                    }
                });
            }
        }
    }
);
