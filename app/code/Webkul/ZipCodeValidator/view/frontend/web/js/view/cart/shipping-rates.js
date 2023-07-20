define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data'
], function ($, ko, _, Component, shippingService, priceUtils, quote, selectShippingMethodAction, checkoutData) {
    'use strict';
    var webkulMessage = '';
    return Component.extend({
        defaults: {
            template: 'Webkul_ZipCodeValidator/shipping-rates'
        },
        isVisible: ko.observable(!quote.isVirtual()),
        isLoading: shippingService.isLoading,
        shippingRates: shippingService.getShippingRates(),
        shippingRateGroups: ko.observableArray([]),
        message: ko.observable(false),
        selectedShippingMethod: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @override
         */
        initObservable: function () {
            var self = this;

            this._super();

            this.shippingRates.subscribe(function (rates) {
                self.shippingRateGroups([]);
                _.each(rates, function (rate) {
                    var carrierTitle = rate['carrier_title'];

                    if (self.shippingRateGroups.indexOf(carrierTitle) === -1) {
                        self.shippingRateGroups.push(carrierTitle);
                    }
                });
            });

            return this;
        },

        /**
         * Get shipping rates for specific group based on title.
         * @returns Array
         */
        getRatesForGroup: function (shippingRateGroupTitle) {
            return _.filter(this.shippingRates(), function (rate) {
                return shippingRateGroupTitle === rate['carrier_title'];
            });
        },

        /**
         * Format shipping price.
         * @returns {String}
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        /**
         * Set shipping method.
         * @param {String} methodData
         * @returns bool
         */
        selectShippingMethod: function (methodData) {
            selectShippingMethodAction(methodData);
            checkoutData.setSelectedShippingRate(methodData['carrier_code'] + '_' + methodData['method_code']);

            return true;
        },

        setCustomMessage: function () {
            if (quote.shippingAddress()) {
                var self = this;
                var zipcode = quote.shippingAddress().postcode;
                var message = '';
                var quotelength = quote.getItems().length;
                var url = window.location.origin;
                var pathname = window.location.pathname.split('/checkout');
                var baseurl = url + pathname[0];
                var productId = [];
                var i;
                for (i in quote.getItems()) {
                    productId.push(parseInt(quote.getItems()[i].product_id));
                }
                if (!quote.shippingAddress().postcode) {
                    webkulMessage = 'Sorry, no quotes are available for this order at this time';
                    self.message(webkulMessage);
                }
                var messageAjax = $.ajax({
                    url : baseurl +"/zipcodevalidator/zipcode/shippingresult",
                    data : {
                        zip :quote.shippingAddress().postcode,
                        productId : productId
                    },
                    type : "GET",
                    success : function (response) {
                        webkulMessage = response.message;
                        self.message(webkulMessage);
                    }
                });
                return true;
            }
        },
    });
});
