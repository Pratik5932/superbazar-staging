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
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'mage/translate',
        'Magento_Checkout/js/model/full-screen-loader',
        'Bss_OrderDeliveryDate/js/model/delivery-form-data',
        'underscore',
        'mage/calendar'
    ],
    function ($, ko, Component, $t, fullScreenLoader, deliveryFormData, _) {
        'use strict';

        $.extend(true, $, {
            calendarConfig: {
                serverTimezoneOffset: parseInt(window.checkoutConfig.bss_delivery_time_zone)
            }
        });

        return Component.extend({
            defaults: {
                template: 'Bss_OrderDeliveryDate/delivery-date',
                listingTimeSlot: ko.observableArray([])
            },

            bssDeliveryEnable: ko.observable(window.checkoutConfig.bss_delivery_enable),

            bssDeliveryTimeSlot: ko.observable(window.checkoutConfig.bss_delivery_has_timeslot),

            bssShippingComment: ko.observable(window.checkoutConfig.bss_shipping_comment),

            dateRequired : ko.observable(window.checkoutConfig.date_field_required),

            timeRequired : ko.observable(window.checkoutConfig.times_field_required),

            commentRequired : ko.observable(window.checkoutConfig.comment_field_required),

            errorDateRequiredValidate : ko.observable(false),

            errorTimeRequiredValidate : ko.observable(false),

            errorCommentRequiredValidate : ko.observable(false),

            //Validate Date
            isValidDateBss: function(dateString) {
                var regEx = /\b\d{4}[\/-]\d{1,2}[\/-]\d{1,2}\b/;
                var dtRegex = /\b\d{1,2}[\/-]\d{1,2}[\/-]\d{4}\b/;
                if(!dateString.match(regEx) && !dateString.match(dtRegex)) return false;  // Invalid format
                return true;
            },

            initialize: function () {
                this._super();
                var self = this;
                if (window.checkoutConfig.on_which_page == 0 || window.checkoutConfig.on_which_page ==1 ) {
                    $(document.body).on("click", '#shipping-method-buttons-container .continue', function(event, ui){
                        if (self.bssValidateField() == false) {
                            return false;
                        }
                    });
                } else {
                    $(document.body).on("click", '.payment-method-content .actions-toolbar .action.checkout', function(event, ui){
                        self.bssValidateField();
                    });
                }
                $(document.body).on("click", '#opc-sidebar .actions-toolbar .action.checkout', function(event, ui){
                    self.bssValidateField();
                });
                $(document.body).on("change", '#payment .bss-delivery #shipping_arrival_date', function(event, ui){
                    if ($(this).val() != window.checkoutConfig.today_date) {
                        self.listingTimeSlot(self.listTimeSlot(false));
                    } else {
                        self.listingTimeSlot(self.listTimeSlot());
                    }
                    self.ajaxSaveToQuote();
                });
                $(document.body).on("change", '#payment .bss-delivery #delivery_time_slot', function(event, ui){
                    self.ajaxSaveToQuote();
                });
                $(document.body).on("change", '.bss-delivery #shipping_arrival_date', function () {
                    if ($(this).val() != window.checkoutConfig.today_date) {
                        self.listingTimeSlot(self.listTimeSlot(false));
                    } else {
                        self.listingTimeSlot(self.listTimeSlot());
                    }
                });
                $(document.body).on("change", '#payment .bss-delivery #shipping_arrival_comments', function(event, ui){
                    setTimeout(function(){ self.ajaxSaveToQuote(); }, 300);
                });
                $(document.body).on("change", '.bss-delivery #shipping_arrival_date,#delivery_time_slot', function(event, ui){
                    self.ajaxSaveToQuoteShipping();
                });
                $(document.body).on("change", '#shipping_arrival_comments', function(event, ui){
                    setTimeout(function(){ self.ajaxSaveToQuoteShipping(); }, 300);
                });

                this.listingTimeSlot(this.listTimeSlot());
            },

            bssValidateField: function() {
                var self = this,
                    flag = true;
                if (window.checkoutConfig.date_field_required) {
                    if ($('#shipping_arrival_date').length) {
                        var shipping_arrival_date = $('#shipping_arrival_date').val();
                        if (shipping_arrival_date) {
                            self.errorDateRequiredValidate(false);
                            self.errorDateRequiredValidate('');
                        } else {
                            self.errorDateRequiredValidate(true);
                            self.errorDateRequiredValidate($t('This is a required field.'));
                            flag = false;
                        }
                    }
                }
                if (window.checkoutConfig.times_field_required) {
                    if ($('#delivery_time_slot').length) {
                        var delivery_time_slot = $('#delivery_time_slot').val();
                        if (delivery_time_slot) {
                            self.errorTimeRequiredValidate(false);
                            self.errorTimeRequiredValidate($t(''));
                        } else {
                            self.errorTimeRequiredValidate(true);
                            self.errorTimeRequiredValidate($t('This is a required field.'));
                            flag = false;
                        }
                    }
                }
                if (window.checkoutConfig.comment_field_required) {
                    if ($('#shipping_arrival_comments').length) {
                        var shipping_arrival_comments = $('#shipping_arrival_comments').val();
                        if (shipping_arrival_comments) {
                            self.errorCommentRequiredValidate(false);
                            self.errorCommentRequiredValidate($t(''));
                        } else {
                            self.errorCommentRequiredValidate(true);
                            self.errorCommentRequiredValidate($t('This is a required field.'));
                            flag = false;
                        }
                    }
                }
                return flag;
            },

            ajaxSaveToQuoteShipping: function() {
                var self = this,
                    data = $('.bss-delivery'),
                    dataPost = data.find(':input[name="shipping_arrival_date"], select[name="delivery_time_slot"], textarea[name="shipping_arrival_comments"]').serialize(),
                    actionUrl = window.checkoutConfig.action_payment_save;

                fullScreenLoader.startLoader();
                if (data.find(':input[name="shipping_arrival_date"]').val() && !this.isValidDateBss(data.find(':input[name="shipping_arrival_date"]').val())) {
                    dataPost = data.find('select[name="delivery_time_slot"], textarea[name="shipping_arrival_comments"]').serialize();
                }
                $.ajax({
                    url: actionUrl,
                    type: 'post',
                    data: dataPost,
                    dataType: 'json',

                    /** @inheritdoc */
                    success: function (response) {
                        fullScreenLoader.stopLoader();
                    },

                    /** @inheritdoc */
                    error: function () {
                        alert({
                            content: $.mage.__('Sorry, something went wrong. Please try again later.')
                        });
                    }
                });
            },

            ajaxSaveToQuote: function() {
                var self = this,
                    dataForm = $('#co-payment-form'),
                    dataPost = dataForm.find(':input[name="shipping_arrival_date"], select[name="delivery_time_slot"], textarea[name="shipping_arrival_comments"]').serialize(),
                    actionUrl = window.checkoutConfig.action_payment_save;

                fullScreenLoader.startLoader();
                if (dataForm.find(':input[name="shipping_arrival_date"]').val() && !this.isValidDateBss(dataForm.find(':input[name="shipping_arrival_date"]').val())) {
                    dataPost = dataForm.find('select[name="delivery_time_slot"], textarea[name="shipping_arrival_comments"]').serialize();
                }
                $.ajax({
                    url: actionUrl,
                    type: 'post',
                    data: dataPost,
                    dataType: 'json',

                    /** @inheritdoc */
                    success: function (response) {
                        fullScreenLoader.stopLoader();
                    },

                    /** @inheritdoc */
                    error: function () {
                        alert({
                            content: $.mage.__('Sorry, something went wrong. Please try again later.')
                        });
                    }
                });
            },

            listTimeSlot: function (isToday = true) {
                var listTimeSlot = window.checkoutConfig.bss_delivery_timeslot;
                var newTimeSlotArray = [];
                newTimeSlotArray.push({'value': '', 'label': $t('Please select delivery time slot'), 'disabled': 0});
                $.each(listTimeSlot, function (index, value) {
                    newTimeSlotArray.push({
                        'value': (value.disabled && isToday) ? '' : value.value,
                        'label': value.label,
                        'disabled': isToday ? value.disabled : 0
                    });
                });
                return newTimeSlotArray;
            },

            initDate: function () {
                var self = this,
                    image = '',
                    icon = false,
                    minDate = this.getMinDate();

                if (window.checkoutConfig.bss_delivery_icon) {
                    image = window.checkoutConfig.bss_delivery_icon;
                    icon = true;
                }
                var initDataConfig = {
                    showsTime: false,
                    controlType: 'select',
                    showOn: "button",
                    buttonImage: image,
                    buttonImageOnly: icon,
                    buttonText: "Select date",
                    dateFormat: window.checkoutConfig.bss_delivery_date_fomat,
                    minDate: minDate,
                    serverTimezoneSeconds: parseInt(window.checkoutConfig.bss_delivery_current_time),
                    serverTimezoneOffset: parseInt(window.checkoutConfig.bss_delivery_time_zone),
                    beforeShowDay: self.configDate
                };
                var deliveryData = deliveryFormData.getSelectedDeliveryData(),
                    dateFormat,
                    checkedDate = undefined;
                _.each(deliveryData, function (delivery) {
                    if (undefined !== delivery.name && undefined !== delivery.value && delivery.name == 'shipping_arrival_date') {
                        initDataConfig.setDate = delivery.value;
                        initDataConfig.currentDate = delivery.value;
                        checkedDate = delivery.value;
                    }
                });
                $("#shipping_arrival_date").calendar(initDataConfig);
                if (checkedDate) {
                    $("#shipping_arrival_date").val(checkedDate);
                }
            },

            initTimeSlot: function() {
                var deliveryData = deliveryFormData.getSelectedDeliveryData(),
                    checkedTimeSlot = undefined;
                _.each(deliveryData, function (delivery) {
                    if (undefined !== delivery.name && undefined !== delivery.value && delivery.name == 'delivery_time_slot') {
                        checkedTimeSlot = delivery.value;
                    }
                });
                return checkedTimeSlot;
            },

            isSelectedOptions(optVal) {
                return optVal == this.initTimeSlot();
            },

            initArrivalComment: function() {
                var deliveryData = deliveryFormData.getSelectedDeliveryData(),
                    arrivalCommentInit = '';
                _.each(deliveryData, function (delivery) {
                    if (undefined !== delivery.name && undefined !== delivery.value && delivery.name == 'shipping_arrival_comments') {
                        arrivalCommentInit = delivery.value;
                    }
                });
                return arrivalCommentInit;
            },

            configDate: function (date) {
                var minTime = jQuery.datepicker._getTimezoneDate().getTime() + (window.checkoutConfig.bss_delivery_process_time - 1) * 60 * 60 * 24 * 1000;
                if (date.getTime() < minTime) {
                    return false;
                }

                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                var day = date.getDay(),
                    block_out_holidays = window.checkoutConfig.bss_delivery_block_out_holidays;
                var day_off_arr = [];
                var day_off = window.checkoutConfig.bss_delivery_day_off;
                if (day_off) {
                    day_off_arr = day_off.split(',');
                }
                for (var i = 0; i < day_off_arr.length; i++) {
                    day_off_arr[i] = parseInt(day_off_arr[i]);
                }
                if (day_off_arr.indexOf(day) == -1 && block_out_holidays.indexOf(string) == -1) {
                    if (!window.currentDateDelivery) {
                        window.currentDateDelivery = string;
                    }
                    return[true, ''];
                }

                return [false, ''];
            },
            /**
             * Get min date
             *
             * saturday is 7, sunday is 0, monday is 1,...
             * extendedDay are days over one week
             * Day of week = DOW
             * window.checkoutConfig.min_date is minimum DOW which must be delivery date, we called it minDate
             * If today is (A) DOW and (A) greater than or equal minDate, then preparing time is minDate - (A)
             * If today is (A) DOW and (A) lesser than minDate, then preparing time is total of saturday(7) - (A) and (minDate - 0)
             */
            getMinDate: function () {
                var dateFormat = window.checkoutConfig.bss_delivery_date_fomat,
                    asProcessingDays = window.checkoutConfig.as_processing_days,
                    minDateConfig = window.checkoutConfig.min_date,
                    deliveryDayOfWeek = parseInt(minDateConfig.dayOfWeek),
                    extendedDay = parseInt(minDateConfig.extendedDay),
                    minDate = new Date();
                if (!isNaN(deliveryDayOfWeek) && !isNaN(extendedDay) && asProcessingDays === false) {
                    if (deliveryDayOfWeek >= minDate.getDay()) {
                        return (deliveryDayOfWeek - minDate.getDay()) + extendedDay * 7;
                    } else {
                        return (deliveryDayOfWeek + (7 - minDate.getDay())) + extendedDay * 7;
                    }
                } else {
                    return 0;
                }
            }
        });
    }
);
