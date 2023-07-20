/**
 * Webkul zipcode js.
 * @category Webkul
 * @package Webkul_ZipCodeValidator
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define([
"jquery",
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('zipcodevalidator.zipcodeadmin', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var csvMsg;
                var zipMsg;
                $('#zipcode_csv').change(function () {
                    $('#zipcode_zip').attr("disabled", "disabled");
                    csvMsg = validateCsv();
                    setSaveButtonClass();
                });
                function validateCsv()
                {
                    var csv = jQuery('input:file[name=csv]').val();
                    var csvExt = csv.split('.');
                    if (csvExt[1] != 'csv') {
                        alert('Please choose a valid csv file.');
                        return false;
                    }
                    return true;
                }
                function setSaveButtonClass()
                {
                    if (csvMsg == false || zipMsg == false) {
                        jQuery('#save').addClass('disabled');
                    } else {
                        jQuery('#save').removeClass('disabled');
                    }
                }
            })
        }
    });
    return $.zipcodevalidator.zipcodeadmin;
});
