define(
    [
    'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, Component, quote) {
        return Component.extend({
            totals: quote.getTotals(),
            isDisplayed: ko.observable(!!window.checkoutConfig.chechoutRewardsPoints),
            getValue: ko.observable(window.checkoutConfig.chechoutRewardsPoints),
            getLogo: ko.observable(window.checkoutConfig.rewardsLogo)
        });
    }
);
