define([
	'Magento_Customer/js/model/customer',
	'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
	'Magento_Checkout/js/action/select-shipping-address',
    'jquery'
], function (customer, addressConverter, quote, selectShippingAddress, $) {
    'use strict';

    return function (Component) {
        return Component.extend({
			validateShippingInformation: function () {
				var shippingAddress,
					addressData,
					loginFormSelector = 'form[data-role=email-with-possible-login]',
					emailValidationResult = customer.isLoggedIn(),
					field;

				if (!quote.shippingMethod()) {
					this.errorValidationMessage($t('Please specify a shipping method.'));

					return false;
				}
				
				if( quote.shippingMethod().carrier_code == 'storepickup') {
					//var stores = $.parseJSON(window.checkoutConfig.shipping.select_store.stores);
						if($('#pickup-store').length == 0) {
							jQuery("#label_method_storepickup_storepickup").parent().trigger("click");
						}
						if ($('#pickup-store').val() == '') {
							this.errorValidationMessage('Please select pickup store.'); 
							return false; 
						}
						if($('[name="pickup_date"]').is(":visible")){
							if($('[name="pickup_date"]').val() == '') {
								this.errorValidationMessage('Please select pickup date.'); 
								return false;
							}
						}
					}

				 return this._super();
			}
        });
    }
});