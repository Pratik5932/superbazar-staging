/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Tvl_MultipleWeight/js/lib/combinatorics',
    'mage/translate'
], function (Component, $, ko, _, Combinatorics) {
    'use strict';

    return Component.extend({
        weightData: window.weightDatas,

        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        /**
         * @override
         */
        initialize: function () {
            this._super();
            var $this = this;
            $('#product-addtocart-button').attr('disabled', 'disabled');
            $this.weight.subscribe(function (newValue) {
                var weights = [], itemQties = [];

                _.each($this.getUpperAndUnderWeight(newValue), function (item) {
                    weights.push(item.total_weight);
                    itemQties.push(item);
                });
                $this.availableWeights(weights);
                $this.itemQties(itemQties);
                var weight = parseInt(newValue),
                    selectedWeight = $this.getWeightCloset(weight);
                if (selectedWeight !== false) {
                    //$this.selectedWeight(selectedWeight);
                }
                $('#product-addtocart-button').removeAttr('disabled');
            });

            $this.selectedWeight.subscribe(function (newValue) {
              //  $("#selected-weight").html(newValue);
                var weight = parseInt(newValue),
                    selectedQty = [],
                    selectedQty1 = [],
                    price = $this.calculatePrice(weight);
                $this.price($this.weightData.currency + price);
                _.each($this.itemQties(), function (item) {
                    if (item.total_weight == weight) {
                        _.each(item.items, function (a) {
                            selectedQty.push(a.weight + ':' + a.qty);
                        });
                        $this.selectedQty(selectedQty.join('|'));
                    }
                });
                _.each($this.itemQties(), function (item) {
                    if (item.total_weight == weight) {
                        _.each(item.items, function (a) {
                            selectedQty1.push(a.weight + '*' + a.qty);
                        });
                       $("#selected-weight-detail").html(selectedQty1.join('+'));
                       $("#display-selected-weight").show();
                    }
                });
                // $("#selected-weight-detail").html(selectedQty1);
                
            });

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe({
                    weight: null,
                    availableWeights: [],
                    selectedWeight: '',
                    price: '',
                    itemQties: [],
                    selectedQty: '',
                    selectedQty1: ''
                });

            return this;
        },

        getWeightCloset: function(weight) {
            var weights = this.availableWeights();
            if (!this.weight()) return false;

            if (weights.indexOf(weight) >= 0) {
                return weight;
            }

            if (weights.length == 1) {
                return  weights[0]
            } else if (weights.length == 2) {
                return weights[1] - weight <= weight - weights[0] ? weights[1] : weights[0];
            }

            return false;
        },

        isAvailableWeight: function() {
            return this.availableWeights().length;
        },

        calculatePrice: function(weight) {
            var priceUnit = 0, price = 0;
            if (weight < 1000) {
                priceUnit = parseFloat(this.weightData.price);
            } else if (weight < 5000) {
                priceUnit = parseFloat(this.weightData.price_one_five);
            } else {
                priceUnit = parseFloat(this.weightData.price_five);
            }

            price = priceUnit * weight / 1000;

            return price.toFixed(2);
        },

        getAllWeight: function() {
            var weights = [];
            _.each(this.weightData.weights, function (item) {
                var weightItem = parseInt(item.weight);
                weights.push(weightItem);
            });
            weights = weights.sort(function(a, b){return a-b});

            return weights;
        },

        getUpperAndUnderWeight: function(weight) {
            if (!this.weightData.weights.length) {
                return [];
            }

            var $this = this,
                weights = this.weightData.weights.sort(function (a, b) {
                    return parseInt(a.weight) - parseInt(b.weight)
                }),
                availableWeight = [],
                firstWeight = parseInt(weights[0].weight),
                min = {
                    total_weight : firstWeight,
                    items: [{weight: firstWeight, qty: 1}]
                },
                max = {
                    total_weight : 0,
                    items: []
                },
                maxQty = this._findMaxQty(weight)
            ;
            for (let i = 0; i < weights.length; i++) {
                var itemWeight = parseInt(weights[i].weight),
                    qty = parseInt(weights[i].qty)
                ;
                if (max.total_weight < weight) {
                    max.total_weight += itemWeight * qty;
                    max.items.push(weights[i]);
                }

                for(let j = 1; j <= maxQty; j++) {
                    if (j * itemWeight == weight) {
                        return [{
                            total_weight : j * itemWeight,
                            items: [{weight: itemWeight, qty: j}]
                        }]
                    }
                    if (j <= qty) {
                        availableWeight.push(itemWeight);
                    }
                }
            }

            availableWeight = availableWeight.sort(function (a, b) {
                return a - b;
            });

            var tmpMaxQty = 0;
            _.each(max.items, function (item) {
                tmpMaxQty += parseInt(item.qty);
            });
            tmpMaxQty = tmpMaxQty < maxQty ? maxQty : tmpMaxQty;
            for (let i = 1; i <= tmpMaxQty; i++) {
                var cmb = Combinatorics.bigCombination(availableWeight, i), item;
                while(item = cmb.next()) {
                    var tmpWeight = $this._sum(item);
                    if (tmpWeight == weight) {
                        return [$this._parseQty(item)];
                    }
                    if (tmpWeight < weight && tmpWeight > min.total_weight) {
                        min = $this._parseQty(item);
                    }
                    if (tmpWeight > weight && tmpWeight < max.total_weight) {
                        max = $this._parseQty(item);
                    }
                }
            }
            if (max.total_weight < weight) {
                return [max];
            }

            if (min.total_weight > weight) {
                return [min];
            }

            return [min, max];
        },

        _parseQty: function(weights) {
            var qtys = [], items = [], total_weight = 0;
            _.each(weights, function (weight) {
                if (typeof qtys[weight] == 'undefined') {
                    qtys[weight] = 0;
                }
                total_weight += weight;
                qtys[weight] += 1;
            });
            _.each(Object.keys(qtys), function (weight) {
                items.push({weight: parseInt(weight), qty: qtys[weight]});
            });

            return {
                total_weight : total_weight,
                items: items
            }
        },

        _findMaxQty: function(weight) {
            var maxQty = 1;
            _.each(this.weightData.weights, function (item) {
                var qty = Math.ceil(weight / parseInt(item.weight));
                if (parseInt(item.qty) < qty && parseInt(item.qty) > maxQty) {
                    maxQty = parseInt(item.qty);
                    return;
                }
                if (qty > maxQty && qty <= parseInt(item.qty)) {
                    maxQty = qty;
                }
            });

            return maxQty;
        },

        _sum: function(array) {
            return array.reduce(function(total, item) {return total + item});
        },

        getAvailableWeights: function () {
            //var weights = this.getAllWeight();
            if (!this.weight()) return [];

            var weight = parseInt(this.weight());

            if (weights.indexOf(weight) >= 0) {
                return [{
                    total_weight : weight,
                    item: [
                        {weight: weight, qty: 1}
                    ]
                }];
            }

            return this.getUpperAndUnderWeight(weight);
        },
    });
});
