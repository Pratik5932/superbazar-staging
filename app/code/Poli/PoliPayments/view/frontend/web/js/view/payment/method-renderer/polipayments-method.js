/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Poli_PoliPayments/payment/polipayments'
            },
            redirectAfterPlaceOrder: false,
            afterPlaceOrder: function () {
				var starturl;
				starturl=url.build('polipayments/checkout/initiate');
                window.location.replace(starturl);
            },           
        });
    }
);
