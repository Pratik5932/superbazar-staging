/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        'mage/url',
        'Webkul_MpStripe/js/action/set-payment-method-action',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (
        ko,
        $,
        Component,
        setPaymentInformationAction,
        selectPaymentMethodAction,
        checkoutData,
        quote,
        totals,
        $t,
        url,
        setPaymentMethodAction,
        additionalValidators,
        redirectOnSuccessAction
    ) {
        'use strict';
        /**
         * stripeConfig contains all the payment configuration
         */
         var stripeConfig = window.checkoutConfig.payment.mpstripe;

         var stripe = Stripe(stripeConfig.api_publish_key);
         /**
          * customerEmail customer email
          */
         var customerEmail = window.customerData.email;
         var customerCountry = window.customerData.defaultCountryId;

        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_MpStripe/payment/stripe',
                    logoUrl:stripeConfig.image_on_form,
                    isCardAvailable:null,
                    isCardAvailableForSave:null,
                    canAddNewCard:null,
                    stripePaymentInit: false,
                    stripePaymentIntent:null,
                    paymentIntentData:null,
                    paymentSource:'customer',
                    stripeCustomerId:null,
                    saveCardForCustomer:false,
                    savedCards:null,
                    email:null,
                    haveSavedCards:false,
                    address:JSON.stringify({}),
                    isSelect:false,
                    card: ''
                },

                /**
                 * @override
                 */
                initObservable: function () {
                    this._super()
                      .observe(
                          [
                          'isCardAvailable',
                          'isCardAvailableForSave',
                          'canAddNewCard',
                          'stripePaymentIntent',
                          'stripePaymentInit',
                          'paymentIntentData',
                          'paymentSource',
                          'stripeCustomerId',
                          'saveCardForCustomer',
                          'email',
                          'haveSavedCards',
                          'address',
                          'isSelect'
                          ]
                      );

                    return this;
                },

                /**
                 * selectPaymentMethod called when payment method is selected
                 *
                 * @return boolean
                 */
                selectPaymentMethod: function () {
                    selectPaymentMethodAction(this.getData());
                    checkoutData.setSelectedPaymentMethod(stripeConfig.method);
                    return true;

                },

                /**
                 * totals set order totals from quote
                 */
                totals: quote.getTotals(),

                /**
                 * getGrandTotal get order grand total
                 *
                 * @return decimal
                 */
                getGrandTotal:function () {
                    var price = 0;
                    if (this.totals()) {
                        price = totals.getSegment('grand_total').value;
                    }
                    return price;
                },

                /**
                 * getData set payment method data for making it available in PaymentMethod Class
                 *
                 * @return object
                 */
                getData: function () {
                    return {
                        'method': stripeConfig.method,
                        'additional_data': {
                            'stripePaymentIntent':this.stripePaymentIntent(),
                            'email':this.email(),
                            'paymentIntentData':this.paymentIntentData()
                        },
                    };
                },

             /**
              * setStripeCustomerId update customer Id
              *
              * @param HtmlObject element
              */
                setStripeCustomerId: function () {
                    this.stripeCustomerId($("input:radio.stripe-token-payment:checked").val());
                    var stripe_customer_id = this.stripeCustomerId();
                    $('.wk_mp_stripe_savedcard_validation').css('display','none');
                    $('#'+stripe_customer_id).css('display','block');
                },

                /**
                 * setSaveCardForCustomer update condition to save customer card or not
                 */
                setSaveCardForCustomer: function () {

                    if ($('.save-card-for-customer').is(':checked')) {
                        this.saveCardForCustomer(1); } else {
                        this.saveCardForCustomer(0); }
                },

                getCustomerSavedCards: function () {
                    this.savedCards = JSON.parse(stripeConfig.saved_cards);
                    if (this.savedCards.length > 0) {
                        this.haveSavedCards(true);
                        return this.savedCards; }
                },


                /**
                 * getImage return stripe logo to show in the popup
                 *
                 * @return string
                 */
                getImage:function () {

                    return stripeConfig.image_on_form;
                },

                /**
                 * getYearList update the year list for a saved card expiry date validation.
                 *
                 * @return array
                 */
                getYearList: function () {
                    var date = new Date,
                        years = [],
                        year = date.getFullYear();
                        years.push('Select Year');
                    for (var i = year; i < year + 20; i++) {
                           years.push(i);
                    }
                    return years;
                },

                /**
                 * getMonthList update the month list for a saved card expiry date validation.
                 *
                 * @return array
                 */
                getMonthList: function () {
                    return ['Select Month',1,2,3,4,5,6,7,8,9,10,11,12];
                },

                /**
                 *
                 */
                hideOpenCardExpiryCredentials: function () {
                    $('.wk_mp_stripe_savedcard_validation').css('display','none');
                },

                /**
                 * validate  to validate the payment method fields at checkout page
                 *
                 * @return boolean
                 */
                validate: function () {
                    var self = this;
                    if (!self.stripePaymentInit()) {
                        this.messageContainer.addErrorMessage({message: $t("Please enter card details properly.")});
                        return false;
                    } else {
                        return true;
                    }
                },

                createPaymentForStripe: function () {
                    var self = this;
                    var style = {
                        base: {
                          color: '#32325d',
                          fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                          fontSmoothing: 'antialiased',
                          fontSize: '16px',
                          '::placeholder': {
                            color: '#aab7c4'
                          }
                        },
                        invalid: {
                          color: '#fa755a',
                          iconColor: '#fa755a'
                        }
                      };
                      
                    var elements = stripe.elements({locale: 'auto'});
                    // Create an instance of the card Element.
                    self.card = elements.create('card', {style: style});
                    // Add an instance of the card Element into the `card-element` <div>.
                    self.card.mount('#card-element');
                    // Handle real-time validation errors from the card Element.
                    self.card.addEventListener('change', function(event) {
                        var displayError = document.getElementById('card-errors');
                        if (event.error || !event.complete) {
                            self.stripePaymentInit(false);
                            if (typeof (event.error) !== 'undefined') {
                                displayError.textContent = event.error.message;
                            }
                        } else {
                            self.stripePaymentInit(true);
                            displayError.textContent = '';
                        }
                    });
                },

                afterPlaceOrder: function () {
                    var self = this;
                    self.redirectAfterPlaceOrder = false;
                    let deferred = $.Deferred();
                    $('body').trigger('processStart');
                    $.ajax({
                        url: stripeConfig.getPaymentIntentUrl,
                        dataType:'json',
                        contentType: "application/json",
                        method: 'get',
                        success: function (response) {
                            if (response.id) {
                                deferred.resolve(response);
                            } else {
                                deferred.reject(response);
                            }
                        },
                        error: function (error) {
                            deferred.reject(error);
                        }
                    });
                    deferred.promise().then(
                        function (data) {
                            var transferData = data;
                            var billingAddress = quote.billingAddress();
                            if (!window.checkoutConfig.quoteData.customer_id || window.checkoutConfig.quoteData.customer_id == null) {
                                var email = quote.guestEmail;
                            } else {
                                var email = window.checkoutConfig.customerData.email;
                            }            
                            var tokenData = {
                                payment_method_data : {
                                    billing_details: {
                                        address:{
                                            city:billingAddress.city,
                                            country:billingAddress.countryId,
                                            line1:billingAddress.street.join(', '),
                                            state:billingAddress.region?billingAddress.region:billingAddress.regionId,
                                            postal_code: billingAddress.postcode
                                        }
                                    }
                                },
                                receipt_email: email
                            }
                            stripe.handleCardPayment(data.client_secret , self.card, tokenData).then(function(result) {
                                if (result.error) {
                                    $.ajax({
                                        url: stripeConfig.cancelOrder,
                                        method: 'post',
                                        data: {
                                            'message' : result.error.message,
                                            'payment_intent_id' : transferData.id
                                        },
                                        success: function (response) {
                                            $('body').trigger('processStop');
                                            window.location.href = stripeConfig.checkoutCart;
                                        },
                                        error: function (error) {
                                            $('body').trigger('processStop');
                                            window.location.href = stripeConfig.checkoutCart;
                                        }
                                    });
                                } else {
                                    if (result.paymentIntent) {
                                        $.ajax({
                                            url: stripeConfig.getPaymentIntentUrl,
                                            dataType:'json',
                                            contentType: "application/json",
                                            method: 'get',
                                            data: {
                                                'paymentIntentMethod':result.paymentIntent.payment_method,
                                                'payment_intent_id' : result.paymentIntent.id
                                            },
                                            success: function (response) {
                                                if (response) {
                                                    self.stripePaymentIntent(result.paymentIntent.id);
                                                    self.email(result.paymentIntent.receipt_email);
                                                    self.paymentIntentData(JSON.stringify(result.paymentIntent));
                                                    $('body').trigger('processStop');
                                                    redirectOnSuccessAction.execute();
                                                }
                                            },
                                            error: function (error) {
                                                $('body').trigger('processStop');
                                                redirectOnSuccessAction.execute();
                                            }
                                        });
                                    } else {
                                        $('body').trigger('processStop');
                                        return self.messageContainer.addErrorMessage({'message': $t('Not able to process card details , please try again')});
                                    }
                                }
                            });
                        },
                        function (error) {
                            
                            console.log(error);
                            self.messageContainer.addErrorMessage({message: $t("something went wrong")});
                        }
                    );

                    
                }
            }
        );
    }
);
