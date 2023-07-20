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
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Bss_OrderDeliveryDate/js/model/payment/additional-validators'
    ], function (
        Component, 
        additionalValidators,
        bssValidator
    ) {
        'use strict';
        additionalValidators.registerValidator(bssValidator);
        return Component.extend({});
    }
);