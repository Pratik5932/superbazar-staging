define([
    'jquery',
	'mage/url',
    'Magento_Checkout/js/model/quote',
	'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate'
], function (jQuery, getUrl, quote, fullScreenLoader, $t) {
	var storeList = window.storeList;
	
	 quote.shippingMethod.subscribe(function (value) {
		 var storeHtml = "<tr class='storepickup-row'><td colspan=4><div class='storepickup-info'></div></td></tr>";
		 var dateHtml = '<div id="storepickup-date" class="storepickup-date-show field required">'
        + '<label for="pickup_date">'+$t('Pickup Date :')+'</label>'
         + '<input name="pickup_date" type="text" id="pickup_date" value="" class="input-text required-entry form-control" readonly>'
        + '</div>' +'<br />';
		 var storepickupMethod = jQuery("#label_method_storepickup_storepickup").parent();
		 if(jQuery(".storepickup-info").length == 0){
			 storepickupMethod.after(storeHtml);
			 jQuery(".storepickup-info").append("<div class='show-store-detail'><label>"+$t('Pickup Store')+"</label></div>");
			 jQuery(".show-store-detail").append("<select id='pickup-store' class='store-detail-select'></select>");
			 jQuery(".store-detail-select").append("<option class='store-detail-item' value=''>"+$t('Please Select Store')+"</option>");
			 jQuery.each(storeList, function (index, el) {
				 jQuery(".store-detail-select").append("<option class='store-detail-item' value='"+el.storepickup_id+"'>"+$t(el.store_name)+"</option>");
			 });
			 jQuery(".storepickup-info").append("<div class='display-storeinfo'></div>");
			 jQuery('.display-storeinfo').hide();
			 jQuery('#pickup-store').change(function () {
			 jQuery.each(storeList, function (index, el) {
				if(el.storepickup_id == jQuery('#pickup-store').val()){
                    fullScreenLoader.startLoader();
					jQuery.ajax({
						type: 'POST',
						url: getUrl.build('storepickup/checkout/store'),
						data: {storeId: jQuery('#pickup-store').val()},
						dataType: 'json',
						success: function(data) {	
							if(data.success == 1){
								if(data.html){
									jQuery('.display-storeinfo').html(data.html);
									if(data.disable_date != 1) {
										if(jQuery("#storepickup-date").length == 0){
											jQuery(".storepickup-info").append(dateHtml);
										} else {
											jQuery('#storepickup-date').show();
										}
									}
									checkoutConfig.quoteData.pickup_store = jQuery("#pickup-store").val();
                                    fullScreenLoader.stopLoader();
									var allDays = "0,1,2,3,4,5,6";
									allDays = allDays.replace(data.working_days,'');
									allDays = allDays.replace(',','');
									console.log(allDays);
									var workingDay = allDays.split(",");
									var workingDays = [];
									jQuery.each(workingDay, function( index, value ) {
									  workingDays.push(parseInt(value));
									});
									jQuery('.display-storeinfo').show();
									jQuery("#pickup_date").datepicker("destroy");
									jQuery("#pickup_date").datepicker( {
										minDate: -0,
										dateFormat: 'mm/dd/yy',
										beforeShowDay: function(day) {
											return [ (jQuery.inArray(day.getDay(),workingDays) == -1) ];
										}
									});
								}
							} else {
								jQuery('.display-storeinfo').hide();
								jQuery('#storepickup-date').hide();
								
							}
						}
					});
				 } else {
					 jQuery('.display-storeinfo').hide();
					 jQuery('#storepickup-date').hide();
				 } 
				 
			 });
			 });
		 
		 }
		 
		 if (quote.shippingMethod().carrier_code == 'storepickup') {
             jQuery(".storepickup-row").show();
			 
		 } else {
			 jQuery(".storepickup-row").hide();
		 }
			 
	 });
	 
});