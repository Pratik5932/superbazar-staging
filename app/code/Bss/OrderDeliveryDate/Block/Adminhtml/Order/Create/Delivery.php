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
namespace Bss\OrderDeliveryDate\Block\Adminhtml\Order\Create;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Delivery extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
{
    /**
     * Helper Bss
     *
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helperBss;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Delivery constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Bss\OrderDeliveryDate\Helper\Data $helperBss
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Bss\OrderDeliveryDate\Helper\Data $helperBss,
        array $data = []
    ) {
        $this->helperBss = $helperBss;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
    }

    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Delivery Date Information');
    }

    /**
     * {@inheritdoc}
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $fieldset = $this->_form->addFieldset(
            'advanced_fieldset',
            ['legend' => '', 'collapsable' => false]
        );

        $dateFormat = $this->helperBss->formatDate();
        $dateFormat = str_replace("m", "M", $dateFormat);
        $fieldset->addField(
            'shipping_arrival_date',
            'date',
            [
                'name' => 'shipping_arrival_date',
                'label' => __('Delivery Date'),
                'title' => __('Delivery Date'),
                'date_format' => $dateFormat
            ]
        );

        $empty = ['value' => '', 'label' => __('-- Please Select --')];
        $options = $this->helperBss->getTimeSlot();
        array_unshift($options, $empty);
        $fieldset->addField(
            'delivery_time_slot',
            'select',
            [
                'name' => 'delivery_time_slot',
                'label' => __('Delivery Time Slot'),
                'title' => __('Delivery Time Slot'),
                'values' => $options,
            ]
        );

        if ($this->helperBss->isShowShippingComment()) {
            $fieldset->addField(
                'shipping_arrival_comments',
                'textarea',
                [
                    'name' => 'shipping_arrival_comments',
                    'label' => __('Shipping Arrival Comment'),
                    'title' => __('Shipping Arrival Comment')
                ]
            );
        }
        
        $this->_form->setValues($this->_getSession()->getOrder()->getData());
        $this->setForm($this->_form);
        return $this;
    }
}
