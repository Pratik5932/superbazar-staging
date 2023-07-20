define(
    [
        'Mirasvit_RewardsCheckout/js/checkout/cart/rewards_points'
    ],
    function(
        cartRewardsPoints
    ) {
        'use strict';

        if (typeof cartRewardsPoints == 'undefined') {
            return null;
        }

        var template = 'Mirasvit_RewardsCheckout/checkout/rewards/checkout/usepoints';
        if (!cartRewardsPoints().isShowRewards() && cartRewardsPoints().rewardsCheckoutNotification) {
            template = 'Mirasvit_RewardsCheckout/checkout/rewards/checkout/notification';
        }

        return cartRewardsPoints.extend({
            defaults: {
                template: template,
                isCheckout: 1
            }
        });
    }
);