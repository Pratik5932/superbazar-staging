define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'uiRegistry',
        'mage/storage',
        'Mirasvit_RewardsCheckout/js/model/messages',
        'Magento_Checkout/js/action/get-payment-information',
        'Mirasvit_RewardsCheckout/js/view/checkout/rewards/points_spend',
        'Mirasvit_RewardsCheckout/js/view/checkout/rewards/points_totals',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-list'
    ],
    function(
        $,
        ko,
        Component,
        registry,
        storage,
        messageContainer,
        getPaymentInformationAction,
        rewardsSpend,
        rewardsEarn,
        getTotals,
        selectShippingAddress,
        addressConverter,
        defaultShippingProcessor,
        rateRegistry,
        quote,
        urlBuilder,
        totals,
        shippingService,
        paymentService,
        paymentMethodList
    ) {
        'use strict';
        var form = '#reward-points-form';

        var rewardsLogo              = ko.observable(window.checkoutConfig.rewardsLogo);
        var isShowRewards            = ko.observable(window.checkoutConfig.chechoutRewardsIsShow);
        var isRemovePoints           = ko.observable(window.checkoutConfig.chechoutRewardsPointsUsed);
        var rewardsPointsUsed        = ko.observable(window.checkoutConfig.chechoutRewardsPointsUsed);
        var rewardsPointsUsedOrigin  = ko.observable(window.checkoutConfig.chechoutRewardsPointsUsed);
        var chechoutRewardsPointsMax = ko.observable(window.checkoutConfig.chechoutRewardsPointsMax);
        var chechoutRewardsIsGuest   = ko.observable(window.checkoutConfig.chechoutRewardsIsGuest);
        var useMaxPoints             = ko.observable(
            window.checkoutConfig.chechoutRewardsPointsUsed == window.checkoutConfig.chechoutRewardsPointsMax
        );
        var addRequireClass          = ko.observable(
            window.checkoutConfig.chechoutRewardsPointsUsed ? "{'required-entry':true}" : '{}'
        );

        var rewardsPointsName      = window.checkoutConfig.chechoutRewardsPointsName;
        var rewardsPointsAvailble  = window.checkoutConfig.chechoutRewardsPointsAvailble;
        var ApplayPointsUrl        = window.checkoutConfig.chechoutRewardsApplayPointsUrl;

        var rewardsCheckoutNotification = window.checkoutConfig.rewardsCheckoutNotification;

        var updateTotalsM21       = window.checkoutConfig.updateTotalsM21;
        var isShippingMinOrderSet = window.checkoutConfig.isShippingMinOrderSet;

        var isMageplazaOsc             = window.checkoutConfig.isMageplazaOsc;
        var isRokanthemesOpCheckout    = window.checkoutConfig.isRokanthemesOpCheckout;
        var isOnestepcheckoutIosc      = window.checkoutConfig.isOnestepcheckoutIosc;
        var isBssOnestepCheckout       = window.checkoutConfig.isBssOnestepCheckout;
        var isAheadOnestepCheckout     = window.checkoutConfig.isAheadOnestepCheckout;
        var isAmastyShippingTableRates = window.checkoutConfig.isAmastyShippingTableRates;
        var isMagestyAppsPaymentFee    = window.checkoutConfig.isMagestyAppsPaymentFee;

        var isShippingInit = false;
        var allowPageReload = false;

        var template = 'Mirasvit_RewardsCheckout/checkout/rewards/usepoints';
        if (!isShowRewards() && rewardsCheckoutNotification) {
            template = 'Mirasvit_RewardsCheckout/checkout/rewards/notification';
        }

        return Component.extend({
            defaults: {
                template: template,
                isCheckout: 0
            },

            isLoading: ko.observable(false),
            rewardsLogo: rewardsLogo,
            isShowRewards: isShowRewards,
            isRemovePoints: isRemovePoints,
            rewardsPointsUsed: rewardsPointsUsed,
            rewardsPointsUsedOrigin: rewardsPointsUsedOrigin,
            useMaxPoints: useMaxPoints,
            addRequireClass: addRequireClass,

            chechoutRewardsIsGuest: chechoutRewardsIsGuest,
            chechoutRewardsPointsMax: chechoutRewardsPointsMax,
            rewardsPointsAvailble: rewardsPointsAvailble,
            rewardsPointsName: rewardsPointsName,

            rewardsCheckoutNotification: rewardsCheckoutNotification,

            ApplayPointsUrl: ApplayPointsUrl,

            updateTotalsM21: updateTotalsM21,
            isShippingMinOrderSet: isShippingMinOrderSet,

            isMageplazaOsc: isMageplazaOsc,
            isRokanthemesOpCheckout: isRokanthemesOpCheckout,
            isOnestepcheckoutIosc: isOnestepcheckoutIosc,
            isBssOnestepCheckout: isBssOnestepCheckout,
            isAheadOnestepCheckout: isAheadOnestepCheckout,
            isAmastyShippingTableRates: isAmastyShippingTableRates,
            isMagestyAppsPaymentFee: isMagestyAppsPaymentFee,
            quote: null,
            cartCache: null,

            isUpdatePoints: true,

            formSubmit: function () {
                if (!allowPageReload) {
                    this.rewardsFormSubmit(false);
                }

                return allowPageReload;
            },
            rewardsFormSubmit: function (isRemove) {
                if (isRemove) {
                    this.addRequireClass('');
                    this.isRemovePoints(1);
                } else {
                    this.addRequireClass("{'required-entry':true}");
                    if (!this.validate()) {
                        this.addRequireClass('');
                        return;
                    }
                    this.isRemovePoints(0);
                }
                this.submit();
            },
            setMaxPoints: function () {
                if (this.useMaxPoints()) {
                    this.useMaxPoints(false);
                    if (this.rewardsPointsUsedOrigin()) {
                        this.rewardsPointsUsed(this.rewardsPointsUsedOrigin());
                    } else {
                        this.rewardsPointsUsed(0);
                    }
                    this.rewardsFormSubmit(true);
                } else {
                    this.useMaxPoints(true);
                    this.rewardsPointsUsed(this.chechoutRewardsPointsMax());
                    this.rewardsFormSubmit();
                }
                return true;
            },
            validatePointsAmount: function () {
                if (parseInt(this.rewardsPointsUsed()) < this.chechoutRewardsPointsMax()) {
                    this.useMaxPoints(false);
                } else {
                    this.useMaxPoints(true);
                    this.rewardsPointsUsed(this.chechoutRewardsPointsMax());
                }
            },
            validate: function() {
                return $(form).validation() && $(form).validation('isValid');
            },
            submit: function () {
                $('input:disabled', form).removeAttr('disabled');//compatibility with some onepagecheckout
                var formData = $(form).serializeArray();
                var data = {};
                $(formData).each(function(i, v) {
                    data[v.name] = v.value;
                });
                this.isLoading(true);
                var self = this;

                if (this.quote.paymentMethod()) {
                    data['payment_method'] = this.quote.paymentMethod()['method'];
                }
                if (quote.shippingAddress() && this.quote.shippingMethod()) {
                    data['shipping_method']  = this.quote.shippingMethod()['method_code'];
                    data['shipping_carrier'] = this.quote.shippingMethod()['carrier_code'];

                    data.address = JSON.stringify(quote.shippingAddress());
                }
                $.ajax({
                    url: this.ApplayPointsUrl,
                    type: 'POST',
                    dataType: 'JSON',
                    data: data,
                    complete: function (data) {
                        var deferred = $.Deferred();
                        getPaymentInformationAction(deferred);
                        $.when(deferred).done(function () {
                            paymentService.setPaymentMethods(
                                paymentMethodList()
                            );
                        });
                        $('#ajax-loader3').hide();
                        $('#control_overlay_review').hide();
                        rewardsSpend().getValue(data.responseJSON.spend_points_formated);
                        if (data.responseJSON.message) {
                            messageContainer.addSuccessMessage({'message': data.responseJSON.message});
                        }

                        if (data.responseJSON) {
                            if (self.isRemovePoints()) {
                                self.useMaxPoints(false);
                                rewardsSpend().isDisplayed(0);
                            } else {
                                rewardsSpend().isDisplayed(1);
                            }
                            self.rewardsPointsUsed(parseInt(data.responseJSON.spend_points));
                            self.rewardsPointsUsedOrigin(self.rewardsPointsUsed());
                        }
                        self.isLoading(false);
                        getTotals([], false);
                    }
                });
            },
            initialize: function(element, valueAccessor, allBindings) {
                this._super();
                var self = this;

                if (!self.updateTotalsM21) {
                    require(['Magento_Checkout/js/model/cart/cache'], function (cartCache) {
                        self.cartCache = cartCache;
                    });
                }
                if (quote) {
                    this.quote = quote;
                    shippingService.getShippingRates().subscribe(function () {
                        var shippingRates = shippingService.getShippingRates()();
                        // wait for shipping calculations
                        if (quote.isVirtual() || (!quote.isVirtual() && shippingRates.length)) {
                            isShippingInit = true;
                        }
                    });
                    this.updatePoints(self.updateTotalsM21);
                    quote.totals.subscribe(function () {
                        if (self.isUpdatePoints) {
                            self.updatePoints();
                        }
                        if (!self.isOnestepcheckoutIosc && !self.isAheadOnestepCheckout && !isMagestyAppsPaymentFee) {
                            self.isUpdatePoints = true;
                        }
                    });
                }
            },
            updatePoints: function(forceUpdate) {
                var request    = $.Deferred();
                var self       = this;
                var data       = {};
                var serviceUrl = urlBuilder.createUrl('/rewards/mine/update', {});
                 // wait for shipping calculations and logged in customer
                if (!forceUpdate && ((!isShippingInit && !this.isCheckout) || this.chechoutRewardsIsGuest()) ||
                    window.checkoutConfig.chechoutRewardsPointsIsLoading
                ) {
                    return;
                }
                if (quote.shippingMethod()) {
                    data = {
                        shipping_method: quote.shippingMethod()['method_code'],
                        shipping_carrier: quote.shippingMethod()['carrier_code']
                    };
                }
                if (quote.paymentMethod()) {
                    data.payment_method = quote.paymentMethod()['method'];
                }
                // Mageplaza onespetcheckout override loader, so we should not call our
                if (!this.isMageplazaOsc) {
                    this.isLoading(true);
                }
                window.checkoutConfig.chechoutRewardsPointsIsLoading = true
                storage.post(
                    serviceUrl, JSON.stringify(data), false
                ).done(
                    function (response) {
                        $('input:disabled', form).removeAttr('disabled'); //compatibility with some onepagecheckout
                        self.rewardsPointsAvailble = response.chechout_rewards_points_availble;
                        self.chechoutRewardsPointsMax(response.chechout_rewards_points_max);
                        self.rewardsPointsUsed(response.chechout_rewards_points_used);
                        self.useMaxPoints(response.chechout_rewards_points_used == response.chechout_rewards_points_max);

                        var rewardsForm = registry.get('checkout.steps.billing-step.payment.afterMethods.rewards-form');
                        if (rewardsForm) {
                            rewardsForm.isRemovePoints(response.chechout_rewards_points_used);
                            rewardsForm.rewardsPointsUsed(response.chechout_rewards_points_used);
                            rewardsForm.rewardsPointsUsedOrigin(response.chechout_rewards_points_used);
                            rewardsForm.chechoutRewardsPointsMax(response.chechout_rewards_points_max);
                            rewardsForm.useMaxPoints(
                                response.chechout_rewards_points_used == response.chechout_rewards_points_max
                            );
                            rewardsForm.rewardsPointsAvailble = response.chechout_rewards_points_availble;
                            rewardsForm.isShowRewards(response.chechout_rewards_points_max);

                        }
                        rewardsSpend().getValue(response.chechout_rewards_points_spend);
                        rewardsSpend().isDisplayed(response.chechout_rewards_points_used > 0);
                        rewardsEarn().getValue(response.chechout_rewards_points);
                        rewardsEarn().isDisplayed(response.chechout_rewards_is_show);

                        self.updateEarnMessage(response.chechout_rewards_points)

                        request.resolve(response);
                        self.isLoading(false);

                        if (self.isShippingMinOrderSet) {
                            self.isUpdatePoints = false
                            if (self.cartCache) {
                                self.cartCache.clear('address');
                            }
                            var address = quote.shippingAddress();
                            if (!address) {
                                var addressFlat = registry.get('checkoutProvider').shippingAddress;
                                address = addressConverter.formAddressDataToQuoteAddress(addressFlat);
                            }
                            if (self.isRokanthemesOpCheckout) {
                                require(['Rokanthemes_OpCheckout/js/model/shipping-rate-service'], function (shippingRateService) {
                                    shippingRateService().stop(true);
                                    selectShippingAddress(address);
                                    shippingRateService().stop(false);
                                });
                            } else {
                                selectShippingAddress(address);
                            }
                            if (self.isBssOnestepCheckout) {
                                require(['Bss_OneStepCheckout/js/model/update-item-service'], function (updateItemService) {
                                    updateItemService.hasUpdateResult(false);
                                });
                            }
                        }

                        if (forceUpdate && self.updateTotalsM21) {
                            getTotals([], false);
                        }
                        window.checkoutConfig.chechoutRewardsPointsIsLoading = false
                    }
                ).fail(
                    function (response) {
                        request.reject(response);
                        window.checkoutConfig.chechoutRewardsPointsIsLoading = false
                    }
                ).always(
                    function () {
                        window.checkoutConfig.chechoutRewardsPointsIsLoading = false
                    }
                );
                if (self.isBssOnestepCheckout && self.isShippingMinOrderSet) {
                    require(['Bss_OneStepCheckout/js/model/update-item-service'], function (updateItemService) {
                        updateItemService.hasUpdateResult(true);
                    });
                }

                return request;
            },
            updateEarnMessage: function(earnedPointsLabel) {
                if (earnedPointsLabel != 0) {
                    $('.reward-message .message-earn-points-label').html(earnedPointsLabel);
                    $('.reward-message .success.message').show();
                    $('.reward-message .success.message > span.message-earn-points').show();
                } else {
                    if ($('.reward-message .success.message > span').length > 1) {
                        $('.reward-message .success.message > span.message-earn-points').hide();
                    } else {
                        $('.reward-message .success.message').hide();
                    }
                }
            }
        });
    }
);
