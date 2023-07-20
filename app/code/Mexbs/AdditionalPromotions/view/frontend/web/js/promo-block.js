define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/totals-processor/default'
], function (
    $,
    modal,
    quote,
    totalsDefaultProvider
    ) {
    'use strict';

    return function(options){
        $(document).ready(function(){
            var showPromoBlockTitleIfPromoBlockNotEmpty = function(){
                if($(".cart-promos-wrapper").html().trim() != ''){
                    $(".cart-promos-wrapper-title").show();
                }
            };
            showPromoBlockTitleIfPromoBlockNotEmpty();


            var reloadPromoProductsBlock = function(){
                $(".cart-promos-wrapper").html('');
                $(".cart-promos-wrapper-title").hide();
                $.ajax({
                    url: options.promoProductsUrl,
                    type: 'post',
                    dataType: 'json',
                    success: function (response) {
                        var promosHtml = '';
                        var promoHtml;
                        for(var promoHtmlIndex in response){
                            promoHtml = "";
                            if(response.hasOwnProperty(promoHtmlIndex)){
                                promoHtml = response[promoHtmlIndex];
                            }
                            if(promoHtml != ""){
                                promosHtml = promosHtml + promoHtml;
                            }
                        }

                        promosHtml += '</div> ';
                        $(".cart-promos-wrapper").html(promosHtml);
                        if(promosHtml.trim() != ''){
                            $(".cart-promos-wrapper-title").show();
                        }
                    }
                });
            };

            var popupTpl = '<aside ' +
                'class="modal-<%= data.type %> <%= data.modalClass %> ' +
                '<% if(data.responsive){ %><%= data.responsiveClass %><% } %> ' +
                '<% if(data.innerScroll){ %><%= data.innerScrollClass %><% } %>"'+
                'data-role="modal"' +
                'data-type="<%= data.type %>"' +
                'tabindex="0">'+
                '    <div data-role="focusable-start" tabindex="0"></div>'+
                '    <div class="modal-inner-wrap"'+
                'data-role="focusable-scope">'+
                '    <div '+
                'class="modal-content" '+
                'data-role="content">' +
                '<button '+
                'class="action-close" '+
                'data-role="closeBtn" '+
                'type="button">'+
                '<span><%= data.closeText %></span>' +
                '</button>' +
                '</div>'+
                '   </div>'+
                '   </aside>';

            var modalOptions = {
                type: 'popup',
                responsive: false,
                innerScroll: false,
                buttons: [],
                popupTpl: popupTpl,
                modalClass: 'cart-promo-add-to-cart-modal-wrapper',
                closeText: 'X'
            };

            $(".cart-promos-wrapper").on("click", "button[data-action='promo-add-to-cart']", function(){
                var ajaxUrl = options.promoAddToCartHtmlUrl;
                var params = { rule_id: $(this).attr("data-promo-id")};
                var displayPopup = true;
                if(($(this).closest(".cart-promo-wrapper").attr("data-some-products-has-options") == 0)
                    && ($(this).closest(".cart-promo-wrapper").attr("data-rule-has-selections") == 0)
                    ){
                    displayPopup = false;
                    ajaxUrl = options.promoAddToCartUrl;
                    var productsAddData = [];
                    $(this).closest(".cart-promo-wrapper").find(".cart-promo-product-group-wrapper").each(function(){
                        productsAddData.push({'product_id' : $(this).attr("data-first-product-id"), 'qty' : $(this).attr('data-group-qty-left-to-select')});
                    });

                    params = { products_add_data: JSON.stringify(productsAddData)};
                }
                $.ajax({
                    url: ajaxUrl,
                    type: 'post',
                    data: params,
                    showLoader: true,
                    dataType: 'json',
                    success: function (response) {
                        if(displayPopup == true){
                            if(!$.isEmptyObject(response)){
                                if(response.added == 'false' && response.hasOwnProperty('html')){
                                    modal(modalOptions, $('.cart-promo-add-to-cart-modal'));
                                    $('.cart-promo-add-to-cart-modal').modal('openModal').html(response.html).trigger('contentUpdated');
                                }
                            }
                        }else{
                            if(response.hasOwnProperty('status')
                                && (response['status'] == 'success')){
                                if(response.hasOwnProperty('cart_html')){
                                    $("form.form.form-cart").replaceWith($(response['cart_html']).find("form.form.form-cart"));
                                }
                            }

                            totalsDefaultProvider.estimateTotals(quote.shippingAddress());
                            reloadPromoProductsBlock();
                            reLoadHints();
                        }
                    }
                });
            });
            $(".cart-promos-wrapper").on("mouseover", ".cart-promo-product-group-wrapper",
                function(){
                    var groupTitleDiv = $(this).find(".cart-promo-product-group-title");
                    if(groupTitleDiv.html() != ''){
                        groupTitleDiv.show();
                    }
                });
            $(".cart-promos-wrapper").on("mouseout", ".cart-promo-product-group-wrapper",
                function(){
                    $(this).find(".cart-promo-product-group-title").hide();
                });

            var currentVisibleStep = 1;

            var hideStep = function(stepNumber){
                $('.cart-add-promo-group-wrapper[data-step-index="'+stepNumber+'"]').hide();
            };
            var showStep = function(stepNumber){
                $('.cart-add-promo-group-wrapper[data-step-index="'+stepNumber+'"]').show();
            };

            var selectionsPerStep = [];

            var getStepSelectionsQtyExclProduct = function(stepNumber, exclProductId){
                var stepSelectedQty = 0;

                if(typeof selectionsPerStep[stepNumber] != 'undefined'){
                    for (var productId in selectionsPerStep[stepNumber]) {
                        if((exclProductId == 0) || (productId != exclProductId)){
                            if (selectionsPerStep[currentVisibleStep].hasOwnProperty(productId)) {
                                stepSelectedQty += parseInt(selectionsPerStep[currentVisibleStep][productId]);
                            }
                        }
                    }
                }

                return stepSelectedQty;
            };

            var isRequiredNumberOfProductsSelected = function(){
                try{
                    var requiredNumberOfProductsForStep = $('.cart-add-promo-group-wrapper[data-step-index="' + currentVisibleStep + '"]').attr("data-group-qty");
                    var stepSelectionsLength = 0;
                    try{
                        stepSelectionsLength = getStepSelectionsQtyExclProduct(currentVisibleStep, 0);
                    }catch(error){}

                    if(stepSelectionsLength < requiredNumberOfProductsForStep){
                        return false;
                    }
                }catch(err) {}
                return true;
            };

            var isAllConfigurationOptionsSelected = function(){
                var allConfigurationsHaveSelectedOption = true;
                try{
                    for(var productId in selectionsPerStep[currentVisibleStep]){
                        $('.cart-add-promo-group-wrapper[data-step-index="' + currentVisibleStep + '"] .cart-add-promo-product-item-info[data-product-id="' + productId + '"] .swatch-attribute')
                            .each(
                            function(){
                                if($(this).find(".swatch-option.selected").length == 0){
                                    allConfigurationsHaveSelectedOption = false;
                                }
                            });
                    }
                }catch(err) {}
                return allConfigurationsHaveSelectedOption;
            };

            var isAllCustomOptionsSelected = function(){
                var allCustomOptionsConfigured = true;
                try{
                    for(var productId in selectionsPerStep[currentVisibleStep]){
                        $('.cart-add-promo-group-wrapper[data-step-index="' + currentVisibleStep + '"] .cart-add-promo-product-item-info[data-product-id="' + productId + '"] .cart-add-promo-product-custom-options-wrapper div.field.required select').each(function(){
                            if($(this).find("option:selected").val() == ''){
                                $(this).addClass("mage-error");
                                allCustomOptionsConfigured = false;
                            }else{
                                $(this).removeClass("mage-error");
                            }
                        });

                        $('.cart-add-promo-group-wrapper[data-step-index="' + currentVisibleStep + '"] .cart-add-promo-product-item-info[data-product-id="' + productId + '"] .cart-add-promo-product-custom-options-wrapper div.field.required input').each(function(){
                            if($(this).val() == ''){
                                $(this).addClass("mage-error");
                                allCustomOptionsConfigured = false;
                            }else{
                                $(this).removeClass("mage-error");
                            }
                        });
                    }
                }catch(err) {}
                return allCustomOptionsConfigured;
            };

            var getMissingProductsText = function(){
                var currentGroupSelected = 0;
                if(typeof selectionsPerStep[currentVisibleStep] != 'undefined'){
                    currentGroupSelected = getStepSelectionsQtyExclProduct(currentVisibleStep, 0);
                }
                var currentGroupQty = $('.cart-add-promo-group-wrapper[data-step-index="' + currentVisibleStep + '"]').attr("data-group-qty");
                var addS = "s";
                if((currentGroupQty - currentGroupSelected) == 1){
                    addS = "";
                }
                return "Please select " + (currentGroupQty - currentGroupSelected) + " more product" + addS;

            };

            var getJSONProductsAddData = function(){
                var productsAddData = [];
                var productAddData;
                var productOptions;
                var productContainer;
                var optionId;
                var matches;
                var productQty;
                var productQtyInputValue;
                $(".cart-add-promo-product-checkbox-container input:checked").each(function(){
                    productContainer = $(this).closest(".cart-add-promo-product-item-info");
                    productOptions = [];
                    productContainer.find(".swatch-attribute").each(function(){
                        productOptions.push({
                            "attribute_id" : $(this).attr("attribute-id"),
                            "option_id" : $(this).find(".swatch-option.selected").attr("option-id")
                        });
                    });
                    productContainer.find(".product-custom-option").each(function(){
                        matches = $(this).attr('name').match(/options\[(.*?)\]/);
                        if(matches.length > 1){
                            optionId = matches[1];
                            productOptions[optionId] = $(this).val();
                        }
                    });

                    productQtyInputValue = productContainer.find(".cart-add-promo-product-item-qty input").val();

                    productQty = 1;
                    if($.isNumeric(productQtyInputValue)
                        && productQtyInputValue > 0){
                        productQty = productQtyInputValue;
                    }

                    productAddData = {
                        'product_id' : productContainer.attr("data-product-id"),
                        'qty' : productQty,
                        'options' : productOptions
                    };
                    productsAddData.push(productAddData);
                });
                return JSON.stringify(productsAddData);
            };

            var updateCurrentStepSelectedText = function(){
                var currentStepSelectedQty = getStepSelectionsQtyExclProduct(currentVisibleStep, 0);
                var currentGroupDiv = $('.cart-add-promo-group-wrapper[data-step-index="' + currentVisibleStep + '"]');
                var currentGroupQty = currentGroupDiv.attr("data-group-qty");
                var selectionStepText = "Selected " + currentStepSelectedQty + " out of " + currentGroupQty;

                currentGroupDiv.find(".cart-add-promo-group-chosen").text(selectionStepText);
            };

            var updateProductQtyOfCurrentStep = function(productId){
                if(typeof selectionsPerStep[currentVisibleStep] == 'undefined'){
                    selectionsPerStep[currentVisibleStep] = [];
                }
                var currentGroupQty = $('.cart-add-promo-group-wrapper[data-step-index="' + currentVisibleStep + '"]').attr("data-group-qty");
                var qtyInput = $('.cart-add-promo-product-item-info[data-product-id="'+productId+'"]').find(".cart-add-promo-product-item-qty input");
                var checkbox = $('.cart-add-promo-product-item-info[data-product-id="'+productId+'"]').find(".cart-add-promo-product-checkbox-container input");

                var productQty = 1;
                if(qtyInput.length > 0
                    && $.isNumeric(qtyInput.val())){
                    productQty = qtyInput.val();
                }

                var qtyLeftToSelect = currentGroupQty - getStepSelectionsQtyExclProduct(currentVisibleStep, productId);

                if(productQty > qtyLeftToSelect){
                    productQty = qtyLeftToSelect;
                }

                if(checkbox.is(":checked")
                    && (productQty > 0)){
                    selectionsPerStep[currentVisibleStep][productId] = productQty;
                    qtyInput.val(productQty);
                }else{
                    checkbox.prop("checked", false);
                    if(typeof selectionsPerStep[currentVisibleStep][productId] != 'undefined'){
                        selectionsPerStep[currentVisibleStep].splice(productId, 1);
                    }

                    qtyInput.val("");
                }
            };

            $("body").on("click", 'button[data-action="go-to-previous-step"]',
                function(){
                    hideStep(currentVisibleStep);
                    currentVisibleStep--;
                    showStep(currentVisibleStep);
                }).on("click", 'div.swatch-option',
                function(){
                    var checkboxContainer = $(this).closest(".cart-add-promo-product-item-info").find(".cart-add-promo-product-checkbox-container");
                    if($(this).hasClass("selected")
                        && !(checkboxContainer.find("input").is(":checked"))){
                        checkboxContainer.trigger("click");
                    }
                    if(isAllConfigurationOptionsSelected()){
                        $(".cart-add-promo-wrapper-error-configurations").hide();
                    }
                }).on("click", '.cart-add-promo-product-checkbox-container',
                function(event){
                    if(!($(this).find('input').is(":checked"))){
                        $(this).find("input").prop("checked", true);
                    }else{
                        $(this).find("input").prop("checked", false);
                    }

                    var productId = $(this).closest('.cart-add-promo-product-item-info').attr("data-product-id");

                    updateProductQtyOfCurrentStep(productId);
                    updateCurrentStepSelectedText();

                    event.stopPropagation();
                    event.preventDefault();
                }).on("click", '.cart-add-promo-wrapper-button-done',
                function(){
                    var allConfigurationOptionsSelected = isAllConfigurationOptionsSelected();
                    var requiredNumberOfProductsSelected = isRequiredNumberOfProductsSelected();
                    var allCustomOptionsSelected = isAllCustomOptionsSelected();
                    if(!allConfigurationOptionsSelected || !allCustomOptionsSelected){
                        $('.cart-add-promo-wrapper-error-configurations[data-step-index="' + currentVisibleStep + '"]').show();
                    }else{
                        $('.cart-add-promo-wrapper-error-configurations[data-step-index="' + currentVisibleStep + '"]').hide();
                    }
                    if(!requiredNumberOfProductsSelected){
                        $('.cart-add-promo-wrapper-error-products[data-step-index="' + currentVisibleStep + '"]').text(getMissingProductsText()).show();
                    }else{
                        $('.cart-add-promo-wrapper-error-products[data-step-index="' + currentVisibleStep + '"]').first().hide();
                    }
                    if(allConfigurationOptionsSelected && allCustomOptionsSelected && requiredNumberOfProductsSelected){
                        $(".cart-promo-add-to-cart-modal-wrapper button.action-close").trigger("click");
                        selectionsPerStep = [];
                        currentVisibleStep = 1;
                        $.ajax({
                            url: options.promoAddToCartUrl,
                            type: 'post',
                            dataType: 'json',
                            showLoader: true,
                            data: { products_add_data: getJSONProductsAddData() },
                            success: function (response) {
                                if(response.hasOwnProperty('status')
                                    && (response['status'] == 'success')){
                                    if(response.hasOwnProperty('cart_html')){
                                        $("form.form.form-cart").replaceWith($(response['cart_html']).find("form.form.form-cart"));
                                    }
                                }

                                totalsDefaultProvider.estimateTotals(quote.shippingAddress());
                                reloadPromoProductsBlock();
                                reLoadHints();
                            }
                        });
                    }

                }).on("click", '.cart-add-promo-wrapper-button-next',
                function(){
                    var allConfigurationOptionsSelected = isAllConfigurationOptionsSelected();
                    var allCustomOptionsSelected = isAllCustomOptionsSelected();
                    var requiredNumberOfProductsSelected = isRequiredNumberOfProductsSelected();
                    if(!allConfigurationOptionsSelected || !allCustomOptionsSelected){
                        $('.cart-add-promo-wrapper-error-configurations[data-step-index="' + currentVisibleStep + '"]').show();
                    }else{
                        $('.cart-add-promo-wrapper-error-configurations[data-step-index="' + currentVisibleStep + '"]').hide();
                    }
                    if(!requiredNumberOfProductsSelected){
                        $('.cart-add-promo-wrapper-error-products[data-step-index="' + currentVisibleStep + '"]').text(getMissingProductsText()).show();
                    }else{
                        $('.cart-add-promo-wrapper-error-products[data-step-index="' + currentVisibleStep + '"]').hide();
                    }

                    if(allConfigurationOptionsSelected
                        && requiredNumberOfProductsSelected
                        && allCustomOptionsSelected){
                        hideStep(currentVisibleStep);
                        currentVisibleStep++;
                        showStep(currentVisibleStep);
                    }
                })
                .on("change", ".cart-add-promo-product-item-qty input", function(){
                    var newProductQty = 0;
                    if($.isNumeric($(this).val())){
                        newProductQty = $(this).val();
                    }
                    var checkboxContainer = $(this).closest(".cart-add-promo-product-item-info").find(".cart-add-promo-product-checkbox-container");
                    var productId = $(this).closest(".cart-add-promo-product-item-info").attr("data-product-id");
                    if(newProductQty > 0){
                        if(!(checkboxContainer.find("input").is(":checked"))){
                            checkboxContainer.find("input").prop("checked", true);
                        }
                    }else{
                        if(checkboxContainer.find("input").is(":checked")){
                            checkboxContainer.find("input").prop("checked", false);
                        }
                    }
                    updateProductQtyOfCurrentStep(productId);
                    updateCurrentStepSelectedText();
                });
        });
    };

});