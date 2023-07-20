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
namespace Bss\OrderDeliveryDate\Block\Express;

class Review extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Config
     */
    protected $salesConfig;

    /**
     * @var array $layoutProcessors
     */
    protected $layoutProcessors;

    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $data;

    /**
     * Review constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        \Bss\OrderDeliveryDate\Helper\Data $helper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
        $this->helper = $helper;
    }

    /**
     * Get Quote Shipping Arrival Date
     *
     * @return false|string
     */
    public function getShippingDate()
    {
        if ($this->getQuote()->getShippingArrivalDate() == '') {
            return '';
        }
        $formatDate = $this->helper->getDateFormat();
        $formatDate = str_replace('dd', 'd', $formatDate);
        $formatDate = str_replace('mm', 'm', $formatDate);
        $formatDate = str_replace('yy', 'Y', $formatDate);
        return date($formatDate, strtotime($this->getQuote()->getShippingArrivalDate()));
    }

    /**
     * Get Bss helper data
     *
     * @return \Bss\OrderDeliveryDate\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get Quote Shipping Time Slot
     *
     * @return string
     */
    public function getShippingTimeslot()
    {
        return $this->getQuote()->getShippingArrivalTimeslot();
    }

    /**
     * Get Quote Shipping Comment
     *
     * @return string
     */
    public function getShippingComment()
    {
        return $this->getQuote()->getShippingArrivalComments();
    }

    /**
     * Check Products in Quote
     *
     * @return bool
     */
    public function showOdd()
    {
        $cartItems = $this->getQuote()->getAllVisibleItems();
        foreach ($cartItems as $cartItem) {
            $productType = $cartItem->getProduct()->getTypeId();
            if ($productType != "downloadable" && $productType != "virtual") {
                return true;
            }
        }
        return false;
    }
}
