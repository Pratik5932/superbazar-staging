define([
    'jquery',
    'priceUtils',
    'priceBox'
], function ($) {
    if (typeof requestUuid == 'undefined') {
        var activeRequests = 0;
        var timeoutId      = null;
        var requestData    = {};
        var requestUuid    = 0;
    }
    $.widget('mage.earnPointsRenderer', {
        options: {},
        pointsXhr: [],
        productData: {},
        priceBoxEl: null,
        productId: null,
        productMainBlock: null,
        isCategoryPage: false,
        isRequestFinish: true,

        _init: function() {
            if (!requestUuid) {
                requestData[this.uuid] = {
                    productData: {},
                    pointsXhr: {},
                    isRequestFinish: true
                };
            }

            var self = this;
            var propVal = [];

            this.initData();

            $('.price-box', this.productMainBlock).on('updatePrice', function(e, data) {
                if (!data || !$('.rewards__product-earn-points', this).length || $('.page-product-bundle').length) {
                    return;
                }

                var option = {};
                if (typeof data.prices == 'undefined') { // configurable
                    for (var i in data) {
                        option   = data[i];
                    }
                } else { //swatches
                    option = data.prices;
                }

                var currentPrice    = self.element.attr('data-rewards-base-price');
                var originPrice     = self.element.attr('data-origin-rewards-base-price')*1;
                var additionalPrice = 0;
                //Point calculation for Customizable Options of the product
                for (var i in data) {
                    try {
                        propVal[i] = data[i].finalPrice.amount;
                    } catch (e) {
                        propVal[i] = 0;
                    }
                    break;
                }

                var customOptSum = 0;
                for (var j in propVal) {
                    customOptSum += propVal[j];
                }
                additionalPrice += customOptSum;

                if (typeof option.rewardProductId != 'undefined') {
                    self.productId = Math.abs(option.rewardProductId.amount);
                }
                if (typeof option.rewardsBasePrice != 'undefined') {
                    additionalPrice = option.rewardsBasePrice.amount;
                }

                var price = additionalPrice + originPrice;
                if (price != currentPrice) {
                    self.element.attr('data-rewards-base-price', price);
                    self.requestPoints();
                }

                return;
            });

            //bundle
            $('#product_addtocart_form').on('updateProductSummary', function(e, data) {
                if ($(self.element).closest('.fieldset-bundle-options').length) {
                    var price = self.element.attr('data-rewards-base-price');
                    self.element.attr('data-rewards-base-price', price);
                    self.requestPoints(1);
                    return;
                }
                var price = 0;
                if (data.config.selected.length) {
                    $.each(data.config.selected, function (index, values) {
                        $.each(values, function (i, value) {
                            if (!value) {
                                return;
                            }
                            var qty = data.config.options[index]['selections'][value]['qty'];
                            price += data.config.options[index]['selections'][value]['rewardsBasePrice']['amount'] * qty;
                        })
                    });
                } else {
                    if (self.element.attr('data-rewards-min-price') > 0 && self.element.closest('.price-from').length) {
                        price = self.element.attr('data-rewards-min-price');
                    }
                    if (self.element.attr('data-rewards-max-price') > 0 && self.element.closest('.price-to').length) {
                        price = self.element.attr('data-rewards-max-price');
                    }
                    if (self.element.attr('data-default-selected-rewards-product-price-amount') > 0 && !self.element.closest('.price-from').length && !self.element.closest('.price-to').length) {
                        price = self.element.attr('data-default-selected-rewards-product-price-amount');
                    }
                }
                var currentPrice = self.element.attr('data-rewards-base-price');

                if (currentPrice != price) {
                    self.element.attr('data-rewards-base-price', price);
                    self.requestPoints(1);
                }
            });

            $('.input-text.qty', self.productMainBlock).keyup(function() {
                if ($('.page-product-bundle').length) {
                    return;
                }
                var qty = $(this).val();
                var parent = $(this).parents('tr');
                if (!parent.length) {
                    parent = $(this).parents('.product-info-main');
                }
                if (!parent.length) {
                    return;
                }

                self.requestPoints(qty);
            });

            if (!$('.page-product-bundle').length) {
                this.requestPoints()
            }
        },
        initData: function() {
            var self              = this;
            this.priceBoxEl       = $(this.element).closest('.price-box');
            this.productId        = $(this.priceBoxEl).attr('data-product-id');
            this.productMainBlock = $(this.element).closest('tr');
            if (!this.productId) {
                this.productId = $(this.element).attr('data-product-id');
            }
            if (!this.productMainBlock.length) {
                this.productMainBlock = $(this.element).closest('.product-info-main');
            }
            // bundle summary
            if (!this.productMainBlock.length) {
                this.productMainBlock = $(this.element).closest('.product-details');
            }
            // category page
            if (!this.productMainBlock.length) {
                this.isCategoryPage = true;
                this.productMainBlock = $(this.element).closest('.product-item-info');
            }
            if (!timeoutId) {
                requestUuid = this.uuid;
                timeoutId = setInterval(function() {
                    if (requestData[requestUuid].isRequestFinish) {
                        self.requestMultiPoints();
                    }
                }, 1000);
            }
        },

        requestPoints: function(newQty) {
            var self      = this;
            var qty       = 0;
            var isUpdated = $('.price', this.element).attr('data-points-updated');
            var price     = parseFloat(this.element.attr('data-rewards-base-price')) || 0;

            if (!this.productMainBlock.length && isUpdated) {
                return;
            }
            if (!this.productMainBlock.length) {
                qty = 1;
            } else {
                qty = $('#qty', this.productMainBlock).val();
            }
            if (!qty) {
                qty = newQty;
            }
            if (!qty) {
                qty = 1;
            }
            if (this.isCategoryPage) {
                if (price <= 0) {
                    if (self.element.attr('data-rewards-min-price') > 0 && self.element.closest('.price-from').length) {
                        price = self.element.attr('data-rewards-min-price');
                    }
                    if (self.element.attr('data-rewards-max-price') > 0 && self.element.closest('.price-to').length) {
                        price = self.element.attr('data-rewards-max-price');
                    }
                    if (self.element.attr('data-default-selected-rewards-product-price-amount') > 0 && !self.element.closest('.price-from').length && !self.element.closest('.price-to').length) {
                        price = self.element.attr('data-default-selected-rewards-product-price-amount');
                    }
                }
            }
            if (!qty || !this.productId) {
                $('.price', self.element).html('');
                return;
            }
            if ((this.element.closest('.price-to').length || this.element.closest('.price-from').length) && isUpdated) {
                return;
            }
            $('.price', self.element).attr('data-points-updated', 1).show();

            $('.points-loader', self.element).show();
            activeRequests++;
            var elemHash = btoa(this.productId+'_'+price);
            $(self.element).attr('data-points-elem', elemHash);
            requestData[requestUuid].productData[elemHash] = {
                'product_id': this.productId,
                'price': price,
                'qty': qty,
                'isAjax': true,
            };
        },

        requestMultiPoints: function() {
            if (activeRequests == 0) {
                return;
            }
            var self             = this;
            requestData[requestUuid].isRequestFinish = false;
            requestData[requestUuid].pointsXhr[this.productId] = $.ajax({
                url: this.options.requestUrl,
                cache: true,
                type: 'POST',
                dataType: 'json',
                data: requestData[requestUuid].productData,
                success: function (data) {
                    for (var k in data) {
                        var element = $('[data-points-elem="' + k + '"]');
                        if (data[k].points > 0) {
                            $('.price', element).html(data[k].label);
                        } else {
                            $('.price', element).html('');
                        }
                        $('.points-loader', element).hide();
                    }
                },
                complete: function() {
                    activeRequests = 0;

                    requestData[requestUuid].isRequestFinish = true;
                    requestData[requestUuid].productData     = {};
                }
            });
        }
    });


    return $.mage.earnPointsRenderer;
});
