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
define([
    'jquery'
    ], function (
        $
    ) {
        'use strict';
        return {
            /**
             * Validate bss delivery
             *
             * @returns {boolean}
             */
            validate: function() {
                if (window.checkoutConfig.date_field_required && $('#shipping_arrival_date').length) {
                    var shipping_arrival_date = $('#shipping_arrival_date').val();
                    if (!shipping_arrival_date) {
                        return false;
                    }
                }
                if (window.checkoutConfig.times_field_required && $('#delivery_time_slot').length) {
                    var delivery_time_slot = $('#delivery_time_slot').val();
                    if (!delivery_time_slot) {
                        return false;
                    }
                }
                if (window.checkoutConfig.comment_field_required && $('#shipping_arrival_comments').length) {
                    var shipping_arrival_comments = $('#shipping_arrival_comments').val();
                    if (!shipping_arrival_comments) {
                        return false;
                    }
                }
                return true;
            }
        }
    }
);