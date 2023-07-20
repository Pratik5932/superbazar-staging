/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/model/customer',
    'Magento_Ui/js/modal/alert'
], function ($, ko, quote, $t, messageList, customer, alert) {
    'use strict';

    var steps = ko.observableArray();

    return {
        steps: steps,
        stepCodes: [],
        validCodes: [],
        isCustomerLoggedIn: customer.isLoggedIn, 

        /**
         * @return {Boolean}
         */
        handleHash: function () {
            var hashString = window.location.hash.replace('#', ''),
                isRequestedStepVisible;

            if (hashString === '') {
                return false;
            }

            if ($.inArray(hashString, this.validCodes) === -1) {
                window.location.href = window.checkoutConfig.pageNotFoundUrl;

                return false;
            }

            isRequestedStepVisible = steps.sort(this.sortItems).some(function (element) {
                return (element.code == hashString || element.alias == hashString) && element.isVisible(); //eslint-disable-line
            });

            //if requested step is visible, then we don't need to load step data from server
            if (isRequestedStepVisible) {
                return false;
            }

            steps().sort(this.sortItems).forEach(function (element) {
                if (element.code == hashString || element.alias == hashString) { //eslint-disable-line eqeqeq
                    element.navigate(element);
                } else {
                    element.isVisible(false);
                }

            });

            return false;
        },

        /**
         * @param {String} code
         * @param {*} alias
         * @param {*} title
         * @param {Function} isVisible
         * @param {*} navigate
         * @param {*} sortOrder
         */
        registerStep: function (code, alias, title, isVisible, navigate, sortOrder) {
            var hash, active;

            if ($.inArray(code, this.validCodes) !== -1) {
                throw new DOMException('Step code [' + code + '] already registered in step navigator');
            }

            if (alias != null) {
                if ($.inArray(alias, this.validCodes) !== -1) {
                    throw new DOMException('Step code [' + alias + '] already registered in step navigator');
                }
                this.validCodes.push(alias);
            }
            this.validCodes.push(code);
            steps.push({
                code: code,
                alias: alias != null ? alias : code,
                title: title,
                isVisible: isVisible,
                navigate: navigate,
                sortOrder: sortOrder
            });
            active = this.getActiveItemIndex();
            steps.each(function (elem, index) {
                if (active !== index) {
                    elem.isVisible(false);
                }
            });
            this.stepCodes.push(code);
            hash = window.location.hash.replace('#', '');

            if (hash != '' && hash != code) { //eslint-disable-line eqeqeq
                //Force hiding of not active step
                isVisible(false);
            }
        },

        /**
         * @param {Object} itemOne
         * @param {Object} itemTwo
         * @return {Number}
         */
        sortItems: function (itemOne, itemTwo) {
            return itemOne.sortOrder > itemTwo.sortOrder ? 1 : -1;
        },

        /**
         * @return {Number}
         */
        getActiveItemIndex: function () {
            var activeIndex = 0;

            steps().sort(this.sortItems).some(function (element, index) {
                if (element.isVisible()) {
                    activeIndex = index;

                    return true;
                }

                return false;
            });

            return activeIndex;
        },

        /**
         * @param {*} code
         * @return {Boolean}
         */
        isProcessed: function (code) {
            var activeItemIndex = this.getActiveItemIndex(),
                sortedItems = steps().sort(this.sortItems),
                requestedItemIndex = -1;

            sortedItems.forEach(function (element, index) {
                if (element.code == code) { //eslint-disable-line eqeqeq
                    requestedItemIndex = index;
                }
            });

            return activeItemIndex > requestedItemIndex;
        },

        /**
         * @param {*} code
         * @param {*} scrollToElementId
         */
        navigateTo: function (code, scrollToElementId) {
            var sortedItems = steps().sort(this.sortItems),
                bodyElem = $('body');

            scrollToElementId = scrollToElementId || null;

            if (!this.isProcessed(code)) {
                return;
            }
            sortedItems.forEach(function (element) {
                if (element.code == code) { //eslint-disable-line eqeqeq
                    element.isVisible(true);
                    bodyElem.animate({
                        scrollTop: $('#' + code).offset().top
                    }, 0, function () {
                        window.location = window.checkoutConfig.checkoutUrl + '#' + code;
                    });

                    if (scrollToElementId && $('#' + scrollToElementId).length) {
                        bodyElem.animate({
                            scrollTop: $('#' + scrollToElementId).offset().top
                        }, 0);
                    }
                } else {
                    element.isVisible(false);
                }

            });
        },

        /**
         * Sets window location hash.
         *
         * @param {String} hash
         */
        setHash: function (hash) {
            window.location.hash = hash;
        },

        /**
         * Next step.
         */
        next: function () {
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
            }else{
                var activeIndex = 0,
                code;

                steps().sort(this.sortItems).forEach(function (element, index) {
                    if (element.isVisible()) {
                        element.isVisible(false);
                        activeIndex = index;
                    }
                });

                if (steps().length > activeIndex + 1) {
                    code = steps()[activeIndex + 1].code;
                    steps()[activeIndex + 1].isVisible(true);
                    this.setHash(code);
                    document.body.scrollTop = document.documentElement.scrollTop = 0;
                }
            }
        }
    };
});
