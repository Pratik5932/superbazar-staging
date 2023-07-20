/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
      'jquery',
      'Magento_Ui/js/modal/alert'
    ],
    function ($,alert,$t) {
        $.widget(
            'webkul.cardJs',
            {
                _create: function () {
                    $('.wk-mp-btn').on('click', function (e) {
                        e.preventDefault();
                        if ($('input[type="checkbox"]:checked').length == 0) {
                            alert({
                                title: 'Attention',
                                content: 'You have not selected any card.'
                            });
                            return false;
                        }
                        $('body').loader('show');
                        $('#form-stripe-validate').submit();
                    });
                },
            }
        );
        return $.webkul.cardJs;
    }
);
