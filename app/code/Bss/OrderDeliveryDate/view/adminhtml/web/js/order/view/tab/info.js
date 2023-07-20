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
require([
    "prototype",
    "Magento_Sales/order/giftoptions_tooltip"
], function () {

//<![CDATA[
    /**
     * Retrieve gift options tooltip content
     */
    function getGiftOptionsTooltipContent(itemId)
    {
        var contentLines = [];
        var headerLine = null;
        var contentLine = null;

        $$('#gift_options_data_' + itemId + ' .gift-options-tooltip-content').each(function (element) {
            if (element.down(0)) {
                headerLine = element.down(0).innerHTML;
                contentLine = element.down(0).next().innerHTML;
                if (contentLine.length > 30) {
                    contentLine = contentLine.slice(0,30) + '...';
                }
                contentLines.push(headerLine + ' ' + contentLine);
            }
        });
        return contentLines.join('<br/>');
    }
    giftOptionsTooltip.setTooltipContentLoaderFunction(getGiftOptionsTooltipContent);
    window.getGiftOptionsTooltipContent = getGiftOptionsTooltipContent;
//]]>

});