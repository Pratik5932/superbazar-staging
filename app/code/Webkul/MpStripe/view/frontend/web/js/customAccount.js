/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpiyzico
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'jquery',
        'mage/template',
        "mage/mage",
        "mage/calendar",
        'Magento_Ui/js/modal/modal'
    ],
    function ($, template) {
        $.widget('webkul.customAccount', {
            _create: function () {
                var self = this;
                var subMerchantType = this.options.accountType;
                if (subMerchantType) {
                    self.initForm(subMerchantType);
                } else {
                    $(document).on("change", "#business_type", function () {
                        var type = $(this).val();
                        self.initForm(type);
                        $('#business_dob').datepicker({
                            dateFormat: 'd/m/yy',
                            changeMonth: true,
                            changeYear: true,
                            maxDate: new Date()
                        });
                        $('#owner_dob').datepicker({
                            dateFormat: 'd/m/yy',
                            changeMonth: true,
                            changeYear: true,
                            maxDate: new Date()
                        });
                    });
                }
            },

            initForm: function (submerchantType) {
                if (submerchantType == 'individual') {
                    var formHtml = template("#individual_template");
                    $('.customaccount-type-wise-fileds').html(formHtml({}));
                    $('.customaccount-company-owner').html('');
                } else if (submerchantType == 'company') {
                    var formHtml = template("#company_template");
                    $('.customaccount-type-wise-fileds').html(formHtml({}));
                    var formHtml = template("#owner_template");
                    $('.customaccount-company-owner').html(formHtml({}));
                } else {
                    $('.customaccount-type-wise-fileds').html('');
                    $('.customaccount-company-owner').html('');
                }
            }
        });

        return $.webkul.customAccount;
    }
);
