/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    "jquery",
    "jquery/ui",
    "mage/calendar"
], function ($) {
    "use strict";
    return function () {
        $( "body" ).on('hover, click','.bss-delivery, #ui-datepicker-div', function() {
            if (!$('#ui-datepicker-div').hasClass('notranslate')) {
                $('#ui-datepicker-div').addClass('notranslate');
            }
        });
    }
});
