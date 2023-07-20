/*global define*/
define(
    [
        'Magento_SalesRule/js/view/summary/discount',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totals) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Mexbs_AdditionalPromotions/cart/totals/discount'
            },

            getDetails: function() {
                var discountSegment = totals.getSegment('discount');
                if (discountSegment && discountSegment.extension_attributes) {
                    return discountSegment.extension_attributes.discount_details;
                }
                return [];
            },

            isDisplayed: function () {
                return (this.getPureValue() != 0);
            },

            isBreakdownDisplayed: function () {
                return (window.checkoutConfig.isApShowBreakdown == 1);
            },

            isBreakdownCollapsedByDefault: function () {
                return (window.checkoutConfig.isApBreakdownCollapsedByDefault == 1);
            }
        });
    }
);
