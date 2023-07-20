<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Superbazaar\General\Plugin\Block\Widget\Grid;


/**
* Adminhtml sales order create search products block
*
* @api
* @author      Magento Core Team <core@magentocommerce.com>
* @since 100.0.2
* @SuppressWarnings(PHPMD.CouplingBetweenObjects)
*/
class Extended
{
    /**
    * Sales config
    *
    * @var \Magento\Sales\Model\Config
    */
    public function afterGetMainButtonsHtml(\Magento\Backend\Block\Widget\Grid\Extended $subject, $result)
    {

        $result .= '<button id="" title="Select All" type="button" class="action-default scalable action-reset action-tertiary" onclick="export_filter_gridJsObject.selectAll()" data-action="grid-filter-reset" data-ui-id="widget-button-3"><span>Select All</span></button>';

        return $result;

    }
}