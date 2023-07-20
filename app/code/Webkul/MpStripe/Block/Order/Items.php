<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block\Order;

class Items extends \Webkul\Marketplace\Block\Order\Items
{
    public function mergeArray($result, $options)
    {
        return array_merge($result, $options);
    }

    public function getOrderinfo($orderId)
    {
        return $this->ordersHelper->getOrderinfo($orderId);
    }
}
