<?php
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
namespace Bss\OrderDeliveryDate\Block\Adminhtml\Order;

class Delivery extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry = null;

    /**
     * Delivery constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     * {@inheritdoc}
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_mode = 'delivery';
        $this->_blockGroup = 'Bss_OrderDeliveryDate';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Delivery Date'));
        $this->buttonList->remove('delete');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        $orderId = $this->coreRegistry->registry('order_id');
        return $this->getUrl('sales/*/view', ['order_id' => $orderId ? $orderId : null]);
    }
}
