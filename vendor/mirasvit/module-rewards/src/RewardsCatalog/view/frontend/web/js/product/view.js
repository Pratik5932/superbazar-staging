require([
    'jquery',
    'priceUtils',
    'priceBox'
], function ($) {
    var appliedOptions = {};
    $('.price-box').on('updatePrice', function(e, data) {
        if (!data || !$('.rewards__product-points').length) {
            return;
        }
        // @todo fix rounding
        var option = {};
        var optionId;
        var rounding      = 0;
        var displayPoints = $('.rewards__product-points .price', this).attr("data-points");
        var text          = $('.rewards__product-points .price', this).attr("data-label");
        var rulePoints    = $('.rewards__product-points .price', this).attr("data-float-points");
        
        if (!text) {
            return;
        }
    
        if (typeof data.prices == 'undefined') { // configurable
            for (var i in data) {
                option   = data[i];
                optionId = i;
                break;
            }
        } else { //swatches
            option = data.prices;
            optionId = 'prices';
        }
        if (typeof option.rewardRules != 'undefined' && typeof option.rewardRules.rounding == 'undefined') {
            rounding = $('.rewards__product-points .price', this).attr("data-current-points-rounding");
        }
        
        if (typeof appliedOptions[optionId] == 'undefined' ||
            option === {} ||
            typeof option.rewardRules == 'undefined' ||
            !option.rewardRules
        ) {
            appliedOptions[optionId] = 0;
        } else {
            appliedOptions[optionId] = option.rewardRules.amount;
        }
        
        newPoints = rulePoints * 1;
        for (var i in appliedOptions) {
            newPoints += appliedOptions[i] * 1;
        }
        
        $('.rewards__product-points .price', this).attr("data-float-new-points", newPoints);
        if (parseInt(rounding) == 1) {
            newPoints = Math.floor(newPoints);
        } else {
            newPoints = Math.ceil(newPoints);
        }
    
        $('.rewards__product-points .price', this).data("used"+optionId, appliedOptions[optionId]);
        $('.rewards__product-points .price', this).attr("data-new-points", newPoints);
        $('.rewards__product-points .price', this).html(text.replace(displayPoints, newPoints));
        //recalc qty
        $('.input-text.qty').keyup();
        
        return;
    });

    //bundle
    $('#product_addtocart_form').on('updateProductSummary', function(e, data) {
        if (!$('.rewards__product-points').length) {
            return;
        }

        var totalPoints = data.config.baseProductPoints;
        $.each(data.config.selected, function(index, values) {
            $.each(values, function(i, value) {
                if (!value) {
                    return;
                }
                var rules      = data.config.options[index]['selections'][value]['rewardRules'];
                var productId  = data.config.options[index]['selections'][value]['optionId'];
                var qty        = data.config.options[index]['selections'][value]['qty'];
                if (rules && typeof rules[productId] != 'undefined') {
                    $.each(rules[productId], function (n, rule) {
                        if (rule.points) {
                            totalPoints += rule.points * qty;
                        } else {
                            var rulePoints = rule.rewardsPrice * qty / rule.coefficient;
                            if (rule.options.limit && rulePoints > rule.options.limit) {
                                rulePoints = rule.options.limit;
                            }
                            totalPoints += rulePoints;
                        }
                    });
                }
            })
        });
        if (totalPoints) {
            if (parseInt(data.config.rounding) == 1) {
                totalPoints = Math.floor(totalPoints);
            } else {
                totalPoints = Math.ceil(totalPoints);
            }
            $('.price-box.price-configured_price .rewards__product-points .price').html(
                totalPoints + ' ' + data.config.rewardLabel
            );
        }
    });

    //
    $('.input-text.qty').keyup(function() {
        var qty = $(this).val();
        if (!qty || qty == 0 || $('.page-product-bundle').length || !$('.rewards__product-points').length) {
            return;
        }
        var parent = $(this).parents('tr');
        if (!parent.length) {
            parent = $(this).parents('.product-info-main');
        }
        if (!parent.length) {
            return;
        }
        var qty        = $(this).val();
        var el         = $('.price-container:not(.price-tier_price) .rewards__product-points .price', parent[0]);
        var mainEl     = el;
        if ($('.price-container.price-tier_price .rewards__product-points .price', parent[0]).length) {
            $('.price-container.price-tier_price .rewards__product-points .price', parent[0]).each(function(i, tierEl) {
                if ($(tierEl).attr('data-price-qty') <= qty) {
                    el = tierEl;
                    return;
                }
            });
        }
        var parentEl   = $('.price-container:not(.price-tier_price) .rewards__product-points', parent[0]);
        var oldPoints  = $(el).attr("data-points");
        var rulePoints = $(el).attr("data-float-new-points");
        var rounding   = $(el).attr("data-current-points-rounding");
        // we need .toFixed because 0.29*100=28.999999... We use 5 to prevent toFixed rounding for cent
        var newPoints  = (rulePoints * qty).toFixed(5);
        var text       = $(el).attr("data-label");
    
        if (rounding == 1) {
            newPoints = Math.floor(newPoints);
        } else {
            newPoints = Math.ceil(newPoints);
        }
        
        if (text) {
            $(mainEl).attr("data-new-points", newPoints).html(text.replace(oldPoints, newPoints));
        }
        if (newPoints) {
            $(parentEl).show();
        } else {
            $(parentEl).hide();
        }
    });
    
    $().ready(function() {
        if (!$('.input-text.qty').length || !$('.rewards__product-points').length) {
            return;
        }
    
        //recalc qty
        $('.input-text.qty').keyup();
    });
});