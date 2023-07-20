define(
    [
        'Magecomp_Paymentfee/js/view/checkout/summary/paymentfee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            isDisplayed: function () {
                return this.getValue(true) != 0;
            }
        });
    }
);