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
namespace Bss\OrderDeliveryDate\Block\System\Form\Field;

class Regexts extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $labelFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->labelFactory = $labelFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     *
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->addColumn('name', ['label' => __('Postcode'), 'style' => 'width:200px']);
        $this->addColumn('from', ['label' => __('From'), 'style' => 'width:150px']);
        $this->addColumn('to', ['label' => __('To'), 'style' => 'width:150px']);
        $this->addColumn('note', ['label' => __('Note'), 'style' => 'width:200px']);
        $this->addColumn('price', ['label' => __('Price'), 'style' => 'width:100px']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('More');
        parent::_construct();
    }

    /**
     * @param string $columnName
     * @return mixed|string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        $columnNameArr = ['note', 'name', 'price'];
        if (!in_array($columnName, $columnNameArr) && isset($this->_columns[$columnName])) {
            /** @var $label \Magento\Framework\View\Design\Theme\Label */
            $options = [
                            ['value' => '12:00 AM','label' => '12:00 AM'],
                            ['value' => '12:30 AM','label' => '12:30 AM'],
                            ['value' => '01:00 AM','label' => '01:00 AM'],
                            ['value' => '01:30 AM','label' => '01:30 AM'],
                            ['value' => '02:00 AM','label' => '02:00 AM'],
                            ['value' => '02:30 AM','label' => '02:30 AM'],
                            ['value' => '03:00 AM','label' => '03:00 AM'],
                            ['value' => '03:30 AM','label' => '03:30 AM'],
                            ['value' => '04:00 AM','label' => '04:00 AM'],
                            ['value' => '04:30 AM','label' => '04:30 AM'],
                            ['value' => '05:00 AM','label' => '05:00 AM'],
                            ['value' => '05:30 AM','label' => '05:30 AM'],
                            ['value' => '06:00 AM','label' => '06:00 AM'],
                            ['value' => '06:30 AM','label' => '06:30 AM'],
                            ['value' => '07:00 AM','label' => '07:00 AM'],
                            ['value' => '07:30 AM','label' => '07:30 AM'],
                            ['value' => '08:00 AM','label' => '08:00 AM'],
                            ['value' => '08:30 AM','label' => '08:30 AM'],
                            ['value' => '09:00 AM','label' => '09:00 AM'],
                            ['value' => '09:30 AM','label' => '09:30 AM'],
                            ['value' => '10:00 AM','label' => '10:00 AM'],
                            ['value' => '10:30 AM','label' => '10:30 AM'],
                            ['value' => '11:00 AM','label' => '11:00 AM'],
                            ['value' => '11:30 AM','label' => '11:30 AM'],
                            ['value' => '12:00 PM','label' => '12:00 PM'],
                            ['value' => '12:30 PM','label' => '12:30 PM'],
                            ['value' => '01:00 PM','label' => '01:00 PM'],
                            ['value' => '01:30 PM','label' => '01:30 PM'],
                            ['value' => '02:00 PM','label' => '02:00 PM'],
                            ['value' => '02:30 PM','label' => '02:30 PM'],
                            ['value' => '03:00 PM','label' => '03:00 PM'],
                            ['value' => '03:30 PM','label' => '03:30 PM'],
                            ['value' => '04:00 PM','label' => '04:00 PM'],
                            ['value' => '04:30 PM','label' => '04:30 PM'],
                            ['value' => '05:00 PM','label' => '05:00 PM'],
                            ['value' => '05:30 PM','label' => '05:30 PM'],
                            ['value' => '06:00 PM','label' => '06:00 PM'],
                            ['value' => '06:30 PM','label' => '06:30 PM'],
                            ['value' => '07:00 PM','label' => '07:00 PM'],
                            ['value' => '07:30 PM','label' => '07:30 PM'],
                            ['value' => '08:00 PM','label' => '08:00 PM'],
                            ['value' => '08:30 PM','label' => '08:30 PM'],
                            ['value' => '09:00 PM','label' => '09:00 PM'],
                            ['value' => '09:30 PM','label' => '09:30 PM'],
                            ['value' => '10:00 PM','label' => '10:00 PM'],
                            ['value' => '10:30 PM','label' => '10:30 PM'],
                            ['value' => '11:00 PM','label' => '11:00 PM'],
                            ['value' => '11:30 PM','label' => '11:30 PM'],
                        ];
            $element = $this->elementFactory->create('select');
            $element->setForm($this->getForm())
                    ->setName($this->_getCellInputElementName($columnName))
                    ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
                    ->setValues($options)
                    ->setStyle('width:110px');
            return str_replace("\n", '', $element->getElementHtml());
        }

        return parent::renderCellTemplate($columnName);
    }
}
