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
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation'
], function ($, alert) {
    'use strict';

    return function (widget) {

        $.widget('mage.orderReview', widget, {
            /**
             * Attempt to submit order
             */
            _submitOrder: function () {
                var self = this;
                window.parentElement = this;
                if ($('#delivery_form').length) {
                    var dataForm = $('#delivery_form');
                    dataForm.validation();
                    if (dataForm.validation('isValid')) {
                        var action = $('#delivery_form').attr('action');
                        $.ajax({
                            url: action,
                            type: 'post',
                            data: dataForm.find(':input[name="shipping_arrival_date"], select[name="delivery_time_slot"], textarea[name="shipping_arrival_comments"]').serialize(),
                            dataType: 'json',

                            /** @inheritdoc */
                            success: function (response) {
                                if (window.parentElement._validateForm()) {
                                    window.parentElement.element.find(window.parentElement.options.updateOrderSelector).fadeTo(0, 0.5)
                                        .end().find(window.parentElement.options.waitLoadingContainer).show()
                                        .end().submit();
                                    window.parentElement._updateOrderSubmit(true);
                                }
                            },

                            /** @inheritdoc */
                            error: function () {
                                alert({
                                    content: $.mage.__('Sorry, something went wrong. Please try again later.')
                                });
                                self._ajaxComplete();
                            }
                        });
                    }
                } else {
                    self._super();
                }
            },

            /**
             * Attempt to ajax submit order
             */
            _ajaxSubmitOrder: function () {
                var self = this;
                if ($('#delivery_form').length) {
                    var dataForm = $('#delivery_form');
                    dataForm.validation();
                    if (dataForm.validation('isValid')) {
                        var action = $('#delivery_form').attr('action');
                        $.ajax({
                            url: action,
                            type: 'post',
                            data: dataForm.find(':input[name="shipping_arrival_date"], select[name="delivery_time_slot"], textarea[name="shipping_arrival_comments"]').serialize(),
                            dataType: 'json',

                            /** @inheritdoc */
                            success: function (response) {
                                
                            },

                            /** @inheritdoc */
                            error: function () {
                                alert({
                                    content: $.mage.__('Sorry, something went wrong. Please try again later.')
                                });
                                self._ajaxComplete();
                            }
                        });
                    }
                    self._super();
                } else {
                    self._super();
                }
            },
        });

        return $.mage.orderReview;
    }
});
