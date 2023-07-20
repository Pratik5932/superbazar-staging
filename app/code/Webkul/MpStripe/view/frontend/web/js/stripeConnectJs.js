/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
      'jquery',
      'mage/translate',
      'mage/mage',
      'mage/validation',
      'jquery/ui'
    ],
    function ($,$t) {

        $.widget(
            'webkul.stripeConnectJs',
            {
                _create: function () {
                    var element = this.element;
                    var self = this;
                    element.on(
                        'click',
                        function () {
                            element.text($t('Saving')+'...');
                            element.parent('div.btn').addClass('disabled');
                            element.css('cursor','default');
                            element.attr('disabled','disabled');
                            window.location = self.getAuth();
                        }
                    );

                    if (this.options.connectData.code != "0" ) {
                        this.savempstripepayment();
                    }

                },

                /**
                 * getAuth redirect to stripe to connect
                 *
                 * @return void
                 */
                getAuth: function () {

                    var str = $(this.options.connectData.mpstrip_payment_form).serializeArray();
                    var calldata = '';
                    var url = '';
                    for (var i=0; i<str.length; i++) {
                        var str2 = str[i];
                        str2['name'] = str2['name'].trim();
                        str2['value'] = str2['value'].trim();
                        calldata += str2['name'] + '=';
                        if (str2['name']=='stripe_user[url]') {
                            calldata += encodeURIComponent(str2['value']) + '&';
                        } else {
                            calldata += str2['value'] + '&';
                        }
                    }
                    calldata = calldata.substring(0, calldata.length - 1);

                    url = 'https://connect.stripe.com/oauth/authorize?'+calldata;
                    return url;
                },

                /**
                 * savempstripepayment function to save stripe seller data
                 *
                 * @return void
                 */
                savempstripepayment: function () {
                    var self=this;
                    var dataForm = $(self.options.connectData.formId);
                    var myForm = dataForm.mage('validation', {});
                    var postUrl = self.options.connectData.postUrl;
                    $(self.options.connectData.formId).trigger('processStart');
                    myForm.validation();
                    if (myForm.validation('isValid')
                    ) {
                        $.ajax(
                            {
                                url:postUrl,
                                data:$(self.options.connectData.formId).serialize(true),
                                method:'post',
                                success:function (data) {

                                    window.location = self.options.connectData.homeUrl;

                                }
                            }
                        );
                    }
                }
            }
        );

        return $.webkul.stripeConnectJs;
    }
);