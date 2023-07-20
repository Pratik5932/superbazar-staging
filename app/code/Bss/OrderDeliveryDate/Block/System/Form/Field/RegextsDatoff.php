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

class RegextsDatoff extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
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
        $this->addColumn('deliverydate_day_off', ['label' => __('Disable Delivery Date'), 'style' => 'width:150px','extra_params' => 'multiple="multiple"']);

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
                            ['value' => '0','label' => 'Sunday'],
                            ['value' => '1','label' => 'Monday'],
                            ['value' => '2','label' => 'Tuesday'],
                            ['value' => '3','label' => 'Wednesday'],
                            ['value' => '4','label' => 'Thursday'],
                            ['value' => '5','label' => 'Friday'],
                            ['value' => '6','label' => 'Saturday'],
                            
                        ];
            $element = $this->elementFactory->create('multiselect');
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
