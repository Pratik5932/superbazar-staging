/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        /**
         * push stripe renderer in the default renderer list
         */
        rendererList.push(
            {
                type: 'mpstripe',
                component: 'Webkul_MpStripe/js/view/payment/method-renderer/stripe'
            }
        );

        return Component.extend({});
    }
);