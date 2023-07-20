define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magecomp_Paymentfee/checkout/summary/paymentfee'
            },
            totals: quote.getTotals(),
            isTaxEnabled: window.paymentFeeConfig.isTaxEnabled,
            displayInclTax: window.paymentFeeConfig.displayInclTax,
            displayExclTax: window.paymentFeeConfig.displayExclTax,
            displayBoth: window.paymentFeeConfig.displayBoth,
            exclTaxPostfix: window.paymentFeeConfig.exclTaxPostfix,
            inclTaxPostfix: window.paymentFeeConfig.inclTaxPostfix,

            isDisplayed: function () {
                return this.getValue(true) != 0;
            },
            getValue: function (clearValue) {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('mc_paymentfee').value;
                }
                if (!clearValue) {
                    return this.getFormattedPrice(price);
                } else {
                    return price;
                }
            },
            getValueExclTax: function () {
                return this.getValue();
            },
            getTitle: function () {
                var title = totals.getSegment('mc_paymentfee').title;
                if(!title){
                    return totals.getSegment('payment_fee_incl_tax').title;
                }
                return title;
            },
            getValueInclTax: function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('payment_fee_incl_tax').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function () {
                var basePrice = 0;
                if (this.totals()) {
                    basePrice = totals.getSegment('base_payment_fee').value;
                }
                return priceUtils.formatPrice(basePrice, quote.getBasePriceFormat());
            }

        });
    }
);
