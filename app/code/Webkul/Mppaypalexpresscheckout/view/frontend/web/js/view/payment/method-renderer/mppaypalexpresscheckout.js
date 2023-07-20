/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Webkul_Mppaypalexpresscheckout/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData,
        setBillingAddressAction,
        globalMessageList
    ) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_Mppaypalexpresscheckout/payment/mppaypalexpresscheckout',
                    billingAgreement: ''
                },
                /**
                 * Redirect to paypal
                 */
                afterPlaceOrder: function () {
                    if (additionalValidators.validate()) {
                        //update payment method information if additional data was changed
                        this.selectPaymentMethod();
                        setPaymentMethodAction(this.messageContainer).done(
                            function () {
                                customerData.invalidate(['cart']);
                                $.mage.redirect(
                                    window.checkoutConfig.payment.mppaypalexpresscheckoutData.redirectUrl[quote.paymentMethod().method]
                                );
                            }
                        );

                        return false;
                    }
                },

                paymentexpresscheckout: function () {
                    this.updateAddresses();

                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.mage.redirect(
                                window.checkoutConfig.payment.mppaypalexpresscheckoutData.redirectUrl[quote.paymentMethod().method]
                            );
                            return false;
                        }
                    );
                },

                /**
                 * Trigger action to update shipping and billing addresses
                 */
                updateAddresses: function () {
                    if (window.checkoutConfig.reloadOnBillingAddress ||
                        !window.checkoutConfig.displayBillingOnPaymentMethod
                    ) {
                        setBillingAddressAction(globalMessageList);
                    }
                },
            }
        );
    }
);
