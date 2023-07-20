define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Mirasvit_RewardsCheckout/js/checkout/cart/rewards_points'
], function (
    ko,
    $,
    Component,
    customerData,
    rewardsPoints
) {
    'use strict';
    
    return Component.extend({
        
        initialize: function () {
            this._super().initChildren();
            return this;
        },
        
        initChildren: function () {
            
            
            this.messages = ko.observable('');
            this.updateRewardsMessage();
            
            
            return this;
        },
        
        updateRewardsMessage: function () {
            var self = this;
            var currentItemsNum = customerData.get('cart')._latestValue.summary_count;
            setInterval(function () {
                
                if (currentItemsNum !== customerData.get('cart')._latestValue.summary_count) {
                    if (customerData.get('cart')._latestValue.summary_count === 0) {
                        $('.reward-message').hide();
                        
                        return this;
                    }
                    
                    var customer = customerData.get('customer');
                    //TODO make api for not loggedin customer
                    if (customer().fullname && customer().firstname) {
                        customerData.reload(['rewards'], true);
                        rewardsPoints().updatePoints(true);
                        currentItemsNum = customerData.get('cart')._latestValue.summary_count;
                    }
                }
            }, 500);
            
            return this;
        }
    });
});
