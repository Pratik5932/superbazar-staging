<?php
/**
 * Product inventory data validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Webkul\MobikulApi\Rewrite\Observer;

class QuantityValidatorObserver extends \Magento\CatalogInventory\Observer\QuantityValidatorObserver
{

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (strpos($_SERVER['REQUEST_URI'],'mobikulhttp') !== false) {
        } else {
            $this->quantityValidator->validate($observer);
        }
    }
}
