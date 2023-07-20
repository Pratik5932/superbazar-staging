/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.popupPlugin', {
        options: {
            twitterLogin: '.twitterlogin',
            linkedinLogin: '.linkedinlogin',
            googleLogin: '.googlelogin',
            instagramLogin: '.instagramlogin'
        },
        _create: function () {
            var self = this;
            $(self.options.twitterLogin).on('click', function (e) {
                self.showSocialSignupPopup(self.options.twitterUrl,self.options.width,self.options.height);
            });
            $(self.options.linkedinLogin).on('click', function (e) {
                self.showSocialSignupPopup(self.options.linkedinUrl,self.options.width,self.options.height);
            });
            $(self.options.googleLogin).on('click', function (e) {
                self.showSocialSignupPopup(self.options.googleUrl,self.options.width,self.options.height);
            });
            $(self.options.instagramLogin).on('click', function (e) {
                self.showSocialSignupPopup(self.options.instagramUrl,self.options.width,self.options.height);
            });
        },
        showSocialSignupPopup: function (url, width, height) {
        
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
        }
        
    });
    return $.mage.popupPlugin;
});
