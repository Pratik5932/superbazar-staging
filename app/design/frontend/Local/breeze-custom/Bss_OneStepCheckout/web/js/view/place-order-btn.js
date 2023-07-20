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
* @category  BSS
* @package   Bss_OneStepCheckout
* @author    Extension Team
* @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
* @license   http://bsscommerce.com/Bss-Commerce-License.txt
*/

define([
    'ko',
    'jquery',
    'uiComponent',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'Bss_OneStepCheckout/js/model/shipping-rate-processor/new-address',
    'Bss_OneStepCheckout/js/model/shipping-rate-processor/customer-address',
    'Bss_OneStepCheckout/js/action/validate-shipping-information',
    'Bss_OneStepCheckout/js/action/validate-gift-wrap-before-order',
    'Bss_OneStepCheckout/js/model/adyen-pay-btn-active',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/totals',
    'mage/url',
    'Magento_Customer/js/model/customer',
    ], function (
        ko,
        $,
        Component,
        registry,
        $t,
        quote,
        bssDefaultProcessor,
        bssCustomerAddressProcessor,
        validateShippingInformationAction,
        validateGiftWrapAction,
        adyenPayBtnActive,
        fullScreenLoader,
        selectBillingAddress,
        additionalValidators,
        shippingService,
        rateRegistry,
        _,
        alert,
        checkoutData,
        totals,
        urlBuilder,
        customer
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Bss_OneStepCheckout/place-order-btn'
            },

            placeOrderLabel: ko.observable($t('Place Order')),

            isVisible: ko.observable(true),

            isCustomerLoggedIn: customer.isLoggedIn, 

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null && quote.paymentMethod() != null),

            multiCheckoutLabel: !quote.isVirtual() ? ko.observable($t('Check Out with Multiple Addresses')) : ko.observable($t('')),

            /** @inheritdoc */
            initialize: function () {
                window.ajaxDoSavedCustomer = false;
                window.isDispatchedRequestCreateCustomer = false;
                this._super();
                var self = this;
                quote.billingAddress.subscribe(function (address) {
                    if (quote.isVirtual()) {
                        setTimeout(function () {
                            self.isPlaceOrderActionAllowed(address !== null && quote.paymentMethod() != null);
                            }, 500);
                    } else {
                        self.isPlaceOrderActionAllowed(address !== null && quote.paymentMethod() != null && quote.shippingMethod() != null);
                    }
                    }, this);
                quote.paymentMethod.subscribe(function (newMethod) {
                    if (quote.isVirtual()) {
                        self.isPlaceOrderActionAllowed(newMethod !== null && quote.billingAddress() != null);
                    } else {
                        self.isPlaceOrderActionAllowed(newMethod !== null && quote.billingAddress() != null && quote.shippingMethod() != null);
                    }

                    if (window.checkoutConfig.paypal_in_context &&
                        newMethod &&
                        newMethod.method === "braintree_paypal"
                    ) {
                        self.isVisible(false);
                    }

                    if (newMethod.method === "adyen_cc") {;
                        if (!adyenPayBtnActive()) {
                            self.isPlaceOrderActionAllowed(false);
                        } else {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    }
                    }, this);
                if (!quote.isVirtual()) {
                    quote.shippingMethod.subscribe(function (method) {
                        var availableRate,
                        shippingRates = shippingService.getShippingRates();
                        if (method) {
                            availableRate = _.find(shippingRates(), function (rate) {
                                return rate['carrier_code'] + '_' + rate['method_code'] === method['carrier_code'] + '_' + method['method_code'];
                            });
                        }
                        self.isPlaceOrderActionAllowed(availableRate && quote.paymentMethod() != null && quote.billingAddress() != null);
                        }, this);
                }

                if (window.checkoutConfig.magento_version >= "2.3.1" &&
                    (window.checkoutConfig.paypal_in_context == true)
                ) {
                    var selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();

                    if ((selectedPaymentMethod == "paypal_express" && window.checkoutConfig.paypal_in_context) ||
                        selectedPaymentMethod == "braintree_paypal") {
                        self.isVisible(false);
                    }

                    $(document).on('change', '.payment-method .radio', function () {
                        if ($('.payment-method._active').find('.actions-toolbar').is('#paypal-express-in-context-button') ||
                            ($(this).attr('id') === 'braintree_paypal')
                        ) {
                            self.isVisible(false);
                        } else {
                            self.isVisible(true);
                        }
                    });
                } else {
                    $(document).on('change', '.payment-method .radio', function () {
                        if ($('.bss-onestepcheckout #adyen_applepay').is(':checked')) {
                            self.isVisible(false);
                        } else {
                            self.isVisible(true);
                        }
                    });
                };
                adyenPayBtnActive.subscribe(function (isAdyenPayActive) {
                    if (quote.paymentMethod().method === "adyen_cc") {
                        if (!isAdyenPayActive) {
                            self.isPlaceOrderActionAllowed(false);
                        } else {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    }
                });
            },

            placeOrder: function (data, event) {

                var isLoggedIn = this.isCustomerLoggedIn();
                var address = quote.shippingAddress();
                var zipcode = (address.postcode);
                var mylocation = window.checkoutConfig.configdata.zipcode 
                var baseUrl = window.checkoutConfig.configdata.baseurl;
                if(isLoggedIn && zipcode != mylocation){
                    /*messageList.addErrorMessage({ message: $t('Profile post code is '+zipcode+' and shipping post code entered is '+mylocation+'. Please change the shipping post code and shop again') });   
                    var body = $('body').loader();
                    body.loader('hide');
                    return false;*/

                    $.ajax(
                        {
                            type: "POST",
                            url: baseUrl+'superbazaar/index/checkproductbyzip',
                            data:'zipcode='+zipcode,
                            success: function (response) {
                                if(response.msg == 'error' && response.value == 1){
                                    var body = $('body').loader();
                                    body.loader('hide');
                                    alert({
                                        title: 'Alert',
                                        content: response.message,
                                        modalClass: 'alert',
                                        actions: {
                                            always: function() {

                                            }
                                        },
                                        buttons: [{
                                            text: $.mage.__('Ok'),
                                            class: 'action primary accept',

                                            /**
                                            * Click handler.
                                            */
                                            click: function () {
                                                this.closeModal(true);
                                            }
                                            }, {
                                                text: $.mage.__('Change "Shipping Post Code" at home page to shop again'),
                                                class: 'action',

                                                /**
                                                * Click handler.
                                                */
                                                click: function () {
                                                    // New action
                                                    console.log('Change "Shipping Post Code" at home page to shop again');
                                                    window.location = baseUrl+'?redirect=home';
                                                }
                                        }]
                                    });
                                }else if(response.msg == 'error' && response.value == 2){
                                    var body = $('body').loader();
                                    body.loader('hide');
                                    console.log(baseUrl);
                                    alert({
                                        title: 'Alert',
                                        content: response.message,
                                        modalClass: 'alert',
                                        actions: {
                                            always: function() {

                                            }
                                        },
                                        buttons: [{
                                            text: $.mage.__('Ok'),
                                            class: 'action primary accept',

                                            /**
                                            * Click handler.
                                            */
                                            click: function () {
                                                this.closeModal(true);
                                            }
                                            }, {
                                                text: $.mage.__('Change "Shipping Post Code" at home page to shop again'),
                                                class: 'action',

                                                /**
                                                * Click handler.
                                                */
                                                click: function () {
                                                    // New action
                                                    console.log('Change "Shipping Post Code" at home page to shop again');
                                                    window.location = baseUrl+'?redirect=home';
                                                }
                                        }]
                                    });
                                }
                            }
                        }
                    );
                    return false;
                }
               if(isLoggedIn){
                    /*messageList.addErrorMessage({ message: $t('Profile post code is '+zipcode+' and shipping post code entered is '+mylocation+'. Please change the shipping post code and shop again') });   
                    var body = $('body').loader();
                    body.loader('hide');
                    return false;*/
                 var self                      = this;
                    $.ajax(
                        {
                            type: "POST",
                            url: baseUrl+'superbazaar/index/checkproductbyzip',
                            data:'zipcode='+zipcode,
                            success: function (response) {
                                if(response.msg == 'error' && response.value == 3){
                                    var body = $('body').loader();
                                    body.loader('hide');
                                    alert({
                                        title: 'Alert',
                                        content: response.message,
                                        modalClass: 'alert',
                                        actions: {
                                            always: function() {
                                                
                                            }
                                        },
                                        buttons: [{
                                            text: $.mage.__('Ok'),
                                            class: 'action primary accept',

                                            /**
                                            * Click handler.
                                            */
                                            click: function () {
                                                this.closeModal(true);
                                            }
                                            }, {
                                                text: $.mage.__('Go to Shopping Cart'),
                                                class: 'action',

                                                /**
                                                * Click handler.
                                                */
                                                click: function () {
                                                    // New action
                                                    console.log('Go to Shopping Cart');
                                                    window.location = baseUrl+'checkout/cart';
                                                }
                                        }]
                                    });
                                }else{
                                self.placeOrderContinue();
                                }
                            }
                        }
                    );
                 return false;  
               }




                var self = this,
                createNewAccountCheckBoxId = 'create-new-customer',
                loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (event) {
                    event.preventDefault();
                }

                if ($(loginFormSelector + ' #' + createNewAccountCheckBoxId).is(":checked")) {
                    var validationLoop = setInterval(function () {
                        var validationValue = additionalValidators.validate();

                        // If ajax do saved customer was done, or validation was proceeded (if guest did not request create account)
                        // we just add place order and do something we need
                        // Vice versa ajax is in processing, do the loop
                        if (window.ajaxDoSavedCustomer) {
                            clearInterval(validationLoop);
                            if (validationValue) {
                                self._resolveQuoteProcessing();
                            }
                        }
                        }, 50);
                } else {
                    if (additionalValidators.validate()) {
                        self._resolveQuoteProcessing();
                    }
                }
                return false;
            },

            placeOrderContinue: function () {
                
                var self                    = this;
                var billingAddressComponent = registry.get('checkout.steps.billing-step.payment.payments-list.billing-address-form-shared');
                var buttonPlaceOrder        = $('input#' + self.getCode()).closest('.payment-method').find('.payment-method-content .actions-toolbar:not([style*="display: none"]) button.action.checkout');
                var selectedPaymentMethod   = checkoutData.getSelectedPaymentMethod();

                    //console.log(selectedPaymentMethod);
                if (billingAddressComponent.isAddressSameAsShipping()) {
                    fullScreenLoader.startLoader();
                    selectBillingAddress(quote.shippingAddress());
                    //fullScreenLoader.stopLoader();
                }
                validateShippingInformationAction().done(
                    function () {
                        var action = 0;
                        if ($('#giftwrap-checkbox input[name="giftwrap"]').is(":checked")) {
                            action = 1;
                        }
                        var fee = $('.giftwrap .amount .price').attr('amount');
                        if (typeof fee === "undefined") {
                            fee = 0;
                        }
                        fee = parseFloat(fee);
                        if (fee < 0) {
                            fee = 0;
                        }
                        validateGiftWrapAction(fee, action).done(
                            function (response) {
                                var res = JSON.parse(response);
                                if (res.status == false || res.gift_wrap_update == true || (res.gift_wrap_display == false && $('#giftwrap-checkbox').length)) {
                                    return;
                                }
                                totals.isLoading(false);
                                if($('.bss-onestepcheckout #adyen_paywithgoogle').is(':checked')) {
                                    $('.bss-onestepcheckout .adyen-checkout__paywithgoogle .gpay-card-info-container').trigger('click');
                                } else if (selectedPaymentMethod && selectedPaymentMethod.indexOf('sagepay') != -1) {
                                    buttonPlaceOrder.not('[style*="display: none"]').trigger('click');
                                } else {
                                    if (selectedPaymentMethod == 'mppaypalexpresscheckout') {
                                        $('#mppaypalexpresscheckout').closest('.payment-method').find('.actions-toolbar button.action').trigger('click');
                                    }
                                    buttonPlaceOrder.trigger('click');
                                }
                            }
                        ).fail(
                            function () {
                                fullScreenLoader.stopLoader();
                            }
                        );
                        if(checkoutData.getSelectedPaymentMethod() == "braintree") {
                            fullScreenLoader.stopLoader();
                        }
                    }
                ).fail(
                    function () {
                        fullScreenLoader.stopLoader();
                    }
                );
            },

            getCode: function () {
                return quote.paymentMethod().method;
            },

            getMultiCheckoutUrl: function () {
                return urlBuilder.build('multishipping/checkout/index');
            },

            isMultiShipping: function () {
                var multiMaximumQty = window.checkoutConfig.bssOsc.multiMaximumQty ? parseInt(window.checkoutConfig.bssOsc.multiMaximumQty): 0,
                totalItems = totals.totals().items_qty;

                if (window.checkoutConfig.bssOsc.isMultiShipping === '1'){
                    return totalItems <= multiMaximumQty;
                }

                return false;
            },
            /**
            * Do place order|quote
            * @private
            */
            _resolveQuoteProcessing: function () {
                var self                      = this;
                var shippingAddressComponent  = registry.get('checkout.steps.shipping-step.shippingAddress');
                var buttonPlaceOrder          = $('input#' + self.getCode()).closest('.payment-method').find('.payment-method-content .actions-toolbar:not([style*="display: none"]) button.action.checkout');
                var selectedPaymentMethod     = checkoutData.getSelectedPaymentMethod();

                if (quote.isVirtual()) {
                    if (selectedPaymentMethod && selectedPaymentMethod.indexOf('sagepay') != -1) {
                        buttonPlaceOrder.not('[style*="display: none"]').trigger('click');
                    } else {
                        buttonPlaceOrder.trigger('click');
                    }
                } else {
                    var amazonLogin = window.checkoutConfig.amazonLogin;
                    if (shippingAddressComponent.validateShippingInformation() ||
                        (!_.isEmpty(amazonLogin) &&
                            amazonLogin.hasOwnProperty('amazon_customer_email') &&
                            amazonLogin.amazon_customer_email && $('.amazon-payment-method').length)) {
                        var reSelectShippingAddress = false;
                        if (typeof window.shippingAddress !== "undefined" || !$.isEmptyObject(window.shippingAddress)) {
                            quote.shippingAddress(window.shippingAddress);
                            var type = quote.shippingAddress().getType();
                            if (type == 'customer-address') {
                                var cache = rateRegistry.get(quote.shippingAddress().getKey());
                                if (!cache) {
                                    reSelectShippingAddress = true;
                                    bssCustomerAddressProcessor(quote.shippingAddress()).done(function (result) {
                                        self.placeOrderContinue();
                                    }).fail(function (response) {

                                    }).always(function () {
                                        window.shippingAddress = {};
                                    });
                                } else {
                                    window.shippingAddress = {};
                                }
                            } else {
                                var cache = rateRegistry.get(quote.shippingAddress().getCacheKey());
                                if (!cache) {
                                    reSelectShippingAddress = true;
                                    bssDefaultProcessor(quote.shippingAddress()).done(function (result) {
                                        self.placeOrderContinue();
                                    }).fail(function (response) {

                                    }).always(function () {
                                        window.shippingAddress = {};
                                    });
                                } else {
                                    window.shippingAddress = {};
                                }
                            }
                        }
                        if (!reSelectShippingAddress) {
                            self.placeOrderContinue();
                        }
                    }
                }
            }
        });
});
