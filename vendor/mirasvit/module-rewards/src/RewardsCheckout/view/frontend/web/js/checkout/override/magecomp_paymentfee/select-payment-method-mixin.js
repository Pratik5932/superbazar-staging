define(
    [
        'Magento_Checkout/js/model/quote',
        'mage/utils/wrapper',
        'Mirasvit_RewardsCheckout/js/checkout/update_payment_method'
    ],
    function(
        quote,
        wrapper,
        updatePaymentMethod
    ) {
        'use strict';
    
        return function (MagecompPaymentfee) {
            return wrapper.wrap(MagecompPaymentfee, function (originalAction, paymentMethod) {
                updatePaymentMethod();
            
                return originalAction(paymentMethod);
            });
        };
    }
);