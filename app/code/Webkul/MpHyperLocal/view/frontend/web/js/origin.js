/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal",
    "googleMapPlaceLibrary"
    ],
    function ($, $t, modal) {
    "use strict";
    $.widget(
        'affiliate.register',
        {
            _create: function () {
                var optionsData = this.options;
                var autocomplete;
                var address_Type = {
                    locality: 'long_name',
                    administrative_area_level_1: 'long_name',
                    country: 'long_name'
                  };
                var addressMap = {
                    locality: 'city',
                    administrative_area_level_1: 'state',
                    country: "country"
                  };
                var selector = {
                    city: 1,
                    state: 2,
                    country: 3
                  };
                // console.log(optionsData.filter);
                    // autocomplete = new google.maps.places.Autocomplete(
                    //     /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
                    //     {types: ['geocode']}
                    // );
                    // // When the user selects an address from the dropdown, populate the address
                    // // fields in the form.
                    // autocomplete.addListener('place_changed', fillInAddress);

                function fillInAddress()
                {
                    var place = autocomplete.getPlace();
                    var data = {};
                    var custAddressType = {
                        'locality' : 'long_name',
                        'administrative_area_level_1' : 'long_name',
                        'country' : 'long_name'
                    };
                    var addressMap = {
                        'locality' : 'city',
                        'administrative_area_level_1' : 'state',
                        'country' : 'country'
                    };
                    $('#autocomplete').attr('data-lat', place.geometry.location.lat());
                    $('#autocomplete').attr('data-lng', place.geometry.location.lng());
                    var address_components = place.address_components;
                    for (var i=0; i<address_components.length; i++) {
                        var addressType = address_components[i]['types'][0];
                        if (typeof custAddressType[addressType] !== 'undefined') {
                            data[addressMap[addressType]] = address_components[i][custAddressType[addressType]];
                        }
                    }
                    if (data.city) {
                        $('#autocomplete').attr('data-city', data.city);
                    } else {
                        $('#autocomplete').attr('data-city', '');
                    }
                    if (data.state) {
                        $('#autocomplete').attr('data-state', data.state);
                    } else {
                        $('#autocomplete').attr('data-state', '');
                    }
                    if (data.country) {
                        $('#autocomplete').attr('data-country', data.country);
                    } else {
                        $('#autocomplete').attr('data-country', '');
                    }

                }

                if ($('#autocompleteform').length > 0) {
                    var autocompleteform;
                    autocompleteform = new google.maps.places.Autocomplete(
                        /** @type {!HTMLInputElement} */(document.getElementById('autocompleteform')),
                        {types: ['geocode']}
                    );

                    // When the user selects an address from the dropdown, populate the address
                    // fields in the form.
                    autocompleteform.addListener('place_changed', fillInPopupAddress);

                    function fillInPopupAddress()
                    {
                    // Get the place details from the autocomplete object.
                        var placepopup = autocompleteform.getPlace();
                        var address = ($('#autocompleteform').val()).split(",");
                        console.log(placepopup.address_components);
                        for (var i = 0; i < placepopup.address_components.length; i++) {
                          var addressType = placepopup.address_components[i].types[0];
                          if (address_Type[addressType]) {
                            var val = placepopup.address_components[i][address_Type[addressType]];
                            if (val == address[0]) {
                                console.log(addressMap[addressType]);
                                $('#address_type>option:eq('+selector[addressMap[addressType]]+')').prop('selected', true);
                            }
                          }
                        }
                        $('#latitude').val(placepopup.geometry.location.lat());
                        $('#longitude').val(placepopup.geometry.location.lng());
                    }
                }
                
                $('#autocomplete').keypress(function () {
                    $(this).css('border','1px solid #c2c2c2');
                });

                $('#address_type').on('change',function() {
                    if ($('#address_type').val() == 'postcode') {
                        $('.location').css('display','none');
                        $('.postcode').css('display','block');
                    } else {
                        $('.postcode').css('display','none');
                        $('.location').css('display','block');
                    }
                });
            }
        }
    );
    return $.affiliate.register;
    }
);
