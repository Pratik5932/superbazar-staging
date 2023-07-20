/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, $, Component, quote, selectPaymentMethodAction, checkoutData, additionalValidators, totals) {
        'use strict';
        var cedwalletconfig = window.checkoutConfig.payment.wallet;
        var status = ko.observable(cedwalletconfig.status);
        var updatebyflag = false;

        return Component.extend({
            defaults: {
                template: 'Ced_Wallet/payment/wallet',
                customerwalletamount: cedwalletconfig.amount,
                updatebyflag:updatebyflag,
                status: status,
                currency: cedwalletconfig.currency,

            },
            totals: quote.getTotals(),
                
            initialize: function () {
                this._super();
                var mainthis = this;
                this.postwalletpayment();
                $("body").delegate(".payment-method .radio", "click", function(){
                    mainthis.changeWalletPayment();
                });
            },
        
            getCode: function() {
                if(checkoutData.getSelectedPaymentMethod('wallet') != "wallet"){
                   
                    if(this.getLeftAmount() >=0 ){
                        $("#checkbox").prop("checked",false);
                    }
                    
                }
                return 'wallet';
            },
            
            getGrandTotal:function(){
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('grand_total').value;
                }
                
                return price;
            },

            getCurrency: function(){
              return this.currency;
            },

            getLeftAmount: function(){
                
                var leftamount = 0;
                if(this.getGrandTotal() ==this.getAmount())
                {
                 leftamount = parseFloat(this.getGrandTotal()-this.getAmount()).toFixed(4);;
                }
                
                leftamount = parseFloat(this.getAmount()-this.getGrandTotal()).toFixed(4);
                
                return leftamount;
            },

            getLeftAmountforPay: function(){
                
                var amountforPay = 0;
                
                if(this.getGrandTotal() > this.getAmount())
                {
                  amountforPay = parseFloat(this.getGrandTotal() - this.getAmount()).toFixed(4);
                  
                }
               
                return amountforPay ;
            },

            changeWalletPayment:function(){
                
                if(this.status()){
                    if(this.getLeftAmount()==0){
                        this.status(false);
                        this.updatebyflag = true;
                        this.postwalletpayment();
                    }
                }
            },

            getAmount: function(){
                return cedwalletconfig.amount;
            },
            
            getData: function() {
                return {
                    "method": this.status()?'wallet':null,
                    "po_number": null,
                    "additional_data": null
                };
            },
            
            getPaymentData: function() {
                return {
                    "method": null,
                    "po_number": null,
                    "additional_data": null
                };
            },
            
            getPostUrl: function(){
                return cedwalletconfig.walleturl;
            },
            
            
            
            isActive: function() {
                return true;
            },
            
            postwalletpayment:function(){
                
                var paymentmethod = this;
                var getstatus = cedwalletconfig.status;
                var walletstatus;
                if(this.status()){
                    walletstatus = 'select';
                }else{
                    walletstatus = 'unselect';
                }
                
                var ajaxreturn = $.ajax({
                    url:this.getPostUrl(),
                    type:"POST",
                    dataType:'json',
                    data:{grandtotal:this.getGrandTotal(),getwallet:walletstatus},
                    success:function(data){
                        
                        if(data >=0){
                            selectPaymentMethodAction(paymentmethod.getData());
                            checkoutData.setSelectedPaymentMethod('wallet');
                        }else{
                            if(!paymentmethod.updatebyflag){
                                selectPaymentMethodAction(paymentmethod.getPaymentData());
                                checkoutData.setSelectedPaymentMethod(null);
                            }                           
                        }
                        if(walletstatus == "select"){
                            $("#checkbox").attr("checked","checked");
                        }
                        paymentmethod.updatebyflag = false;
                        return true;
                    }
                });
                if(ajaxreturn){
                    return true;
                }
            }
           
        });
    }
);
