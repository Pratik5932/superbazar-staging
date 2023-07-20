var config = {
    map: {
        '*': {
            'mirasvit/rewards/onestepcheckout/securecheckout': 'Mirasvit_RewardsCheckout/js/onestepcheckout/securecheckout'
        }
    },
    config: {
        mixins: {
            'Magecomp_Paymentfee/js/action/select-payment-method': {
                'Mirasvit_RewardsCheckout/js/checkout/override/magecomp_paymentfee/select-payment-method-mixin': true
            },
            'Magento_Checkout/js/action/select-payment-method': {
                'Mirasvit_RewardsCheckout/js/checkout/override/select-payment-method-mixin': true
            }
        }
    }
};
