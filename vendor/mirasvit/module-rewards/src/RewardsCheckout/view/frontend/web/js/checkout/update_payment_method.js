define(
    [
        'jquery',
        'uiRegistry',
        'Magento_Checkout/js/model/quote'
    ],
    function(
        $,
        registry,
        quote
    ) {
        'use strict';
        return function (data) {
            require([
                'Magento_Checkout/js/model/totals',
                'Mirasvit_RewardsCheckout/js/view/checkout/rewards/points_totals',
                'Magento_Customer/js/customer-data'
            ], function(totals, rewardsEarn, customerData) {
                if (typeof data == 'undefined') {
                    data = {payment: quote.paymentMethod()};
                } else if (typeof data['payment'] == 'undefined') {
                    data.payment = quote.paymentMethod();
                }
                if (quote.shippingMethod()) {
                    data.shipping_method = quote.shippingMethod()['method_code'];
                    data.shipping_carrier = quote.shippingMethod()['carrier_code'];
                }
                if (typeof customerData.get('customer')().fullname == 'undefined') {
                    return;
                }
                totals.isLoading(true);
                $.ajax({
                    url: window.checkoutConfig.chechoutRewardsPaymentMethodPointsUrl,
                    type: 'POST',
                    dataType: 'JSON',
                    data: data,
                    complete: function (data) {
                        var rewardsForm = registry.get('checkout.steps.billing-step.payment.afterMethods.rewards-form');
                        if (!rewardsForm) {
                            rewardsForm = registry.get('block-rewards-points-form.rewards-points');
                        }
                        if (rewardsForm) {
                            rewardsForm.isRemovePoints(data.responseJSON.chechoutRewardsPointsUsed);
                            rewardsForm.rewardsPointsUsed(data.responseJSON.chechoutRewardsPointsUsed);
                            rewardsForm.rewardsPointsUsedOrigin(data.responseJSON.chechoutRewardsPointsUsed);
                            rewardsForm.chechoutRewardsPointsMax(data.responseJSON.chechoutRewardsPointsMax);
                            rewardsForm.useMaxPoints(
                                data.responseJSON.chechoutRewardsPointsUsed == data.responseJSON.chechoutRewardsPointsMax
                            );
                            rewardsForm.rewardsPointsAvailble = data.responseJSON.chechoutRewardsPointsAvailble;
                            rewardsForm.isShowRewards(data.responseJSON.chechoutRewardsIsShow);

                        }
                        rewardsEarn().isDisplayed(data.responseJSON.success);
                        rewardsEarn().getValue(data.responseJSON.points);
                        totals.isLoading(false);
                    },
                    error: function (data) {
                        totals.isLoading(false);
                    }
                });
            });
        }
    }
);
