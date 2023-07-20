/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/action/check-email-availability',
    'Magento_Customer/js/action/login',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'mage/validation'
], function ($, Component, ko, customer, checkEmailAvailability, loginAction, quote, checkoutData, fullScreenLoader, globalMessageList, $t) {
    'use strict';
    var sosignupconf = window.checkoutConfig.webkul_socialsignup;
    var isCustomerLoggedIn = window.checkoutConfig.isCustomerLoggedIn;
    return Component.extend({
        defaults: {
            template: 'Webkul_SocialSignup/form/element/socialsignupBilling',
        },
        initialize: function () {
            var self = this;
            this._super().initObservable();
            this.showMessage();
            this.socialLogin();
            this.faceBookLogin();
        },
        customerSession: window.checkoutConfig.isCustomerLoggedIn,
        status: sosignupconf.status,
        fb_status: sosignupconf.fb_status,
        google_status: sosignupconf.google_status,
        twitter_status: sosignupconf.twitter_status,
        linkedin_status: sosignupconf.linkedin_status,
        insta_status: sosignupconf.insta_status,
        fbAppId: sosignupconf.fbAppId,
        uId: sosignupconf.uId,
        localeCode: sosignupconf.localeCode,
        fbLoginUrl: sosignupconf.fbLoginUrl,
        loginImg: sosignupconf.loginImg,
        twitterLoginImg: sosignupconf.twitterLoginImg,
        googleLoginImg: sosignupconf.googleLoginImg,
        LinkedinLoginImg: sosignupconf.LinkedinLoginImg,
        InstaLoginImg: sosignupconf.InstaLoginImg,
        socialSignupModuleEnable:  sosignupconf.socialSignupModuleEnable,
        options: {
            twitterLogin: '#twitterlogin-billing',
            linkedinLogin: '#linkedinlogin-billing',
            googleLogin: '#googlelogin-billing',
            instagramLogin: '.instagramlogin-billing',
            fbLogin: '#fblogin-billing',
            actionButton :'login .actions-toolbar,.create .actions-toolbar',
            socialContainer : ".wk_socialsignup_container"
        },
        socialLogin: function () {
        
            var self = this;
            $('body').on('click', self.options.twitterLogin, function (e) {
                self.showSocialSignupPopup(sosignupconf.popupData.twitterUrl,sosignupconf.popupData.width,sosignupconf.popupData.height);
            });
            $('body').on('click', self.options.linkedinLogin, function (e) {
                self.showSocialSignupPopup(sosignupconf.popupData.linkedinUrl,sosignupconf.popupData.width,sosignupconf.popupData.height);
            });
            $('body').on('click', self.options.googleLogin, function (e) {
                self.showSocialSignupPopup(sosignupconf.popupData.googleUrl,sosignupconf.popupData.width,sosignupconf.popupData.height);
            });
            $('body').on('click', self.options.instagramLogin, function (e) {
                self.showSocialSignupPopup(sosignupconf.popupData.instagramUrl,sosignupconf.popupData.width,sosignupconf.popupData.height);
            });
        },
        showSocialSignupPopup: function (url, width, height) {
        
            var url = url+"is_checkoutPageReq/1";
            var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft;
            var screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop;
            var outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth;
            var outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22);
            var left = parseInt(screenX + ((outerWidth - width) / 2), 10);
            var top = parseInt(screenY + ((outerHeight - height) / 2.5), 10);
            var scroller = 1;
            var settings = (
                'width=' + width +
                ',height=' + height +
                ',left=' + left +
                ',top=' + top +
                ',scrollbars=' + scroller
                );
            var newwindow = window.open(url, '', settings);
            if (window.focus) {
                newwindow.focus()
            }
            return false;
        },
        faceBookLogin: function () {
        
            var self = this;
            $(self.options.actionButton).append($(self.options.socialContainer));
            window.fbAsyncInit = function () {
                FB.init({
                    appId: sosignupconf.fbAppId,
                    status     : true,
                    cookie     : true,
                    xfbml      : true,
                    oauth      : true
                });
                FB.getLoginStatus(function (response) {
                    if (response.status == 'connected') {
                        if (isCustomerLoggedIn && sosignupconf.uId) {
                            self.greet(sosignupconf.uId);
                        }
                    }
                });
            };
            (function (d) {
                var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {
                    return;}
                js = d.createElement('script'); js.id = id; js.async = true;
                js.src = "//connect.facebook.net/"+sosignupconf.localeCode+"/all.js";
                d.getElementsByTagName('head')[0].appendChild(js);
            }(document));
            $('body').on('click', self.options.fbLogin, function (e) {
                self.fblogin();
            });
        },
        greet: function (id) {
            FB.api('/me', function (response) {
                var src = 'https://graph.facebook.com/'+id+'/picture';
                if ($('.welcome-msg').length >= 1) {
                    $('.welcome-msg')[0].insert('<img height="20" src="'+src+'"/>');
                }
                if ($('.welcome').length >= 1) {
                    $('.welcome')[0].insert('<img height="20" src="'+src+'"/>');
                }
            });
        },
        login: function () {
            var self = this;
            document.location.href=sosignupconf.fbLoginUrl+"is_checkoutPageReq/1";
        },
        fblogin: function () {
            var self = this;
            FB.login(function (response) {
                if (response.status == 'connected') {
                    self.login();
                } else {
                    // user is not logged in
                    window.location.reload();
                }
            }, {scope:'email'});
            return false;
        },
        
        showMessage : function () {
            var messageContainer = messageContainer || globalMessageList;
            $.ajax({
                url: sosignupconf.getMessagesUrl,
                type: "GET",
                success: (response) => {
                    if (response.errorMsg) {
                        messageContainer.addErrorMessage({
                            'message': response.errorMsg
                        });
                    }
                    if (response.successMsg) {
                        messageContainer.addSuccessMessage({
                            'message': response.successMsg
                        });
                    }                    
                },
                error : (err) => {
                    console.log(err);
                }
            });
        }
    });
});