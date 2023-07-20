/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
var config = {
    "map": {
        "*": {
        }
    },
    config: {
        mixins: {
            'Magento_Paypal/js/order-review': {
                'Bss_OrderDeliveryDate/js/order-review': true
            },
            'Magento_Braintree/js/view/payment/method-renderer/paypal': {
                'Bss_OrderDeliveryDate/js/view/paypal': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Bss_OrderDeliveryDate/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/model/place-order': {
                'Bss_OrderDeliveryDate/js/model/place-order-mixin': true
            }
        }
    }
};
