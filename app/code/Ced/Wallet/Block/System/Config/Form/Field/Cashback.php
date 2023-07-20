<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Wallet\Block\System\Config\Form\Field;

/**
 * Backend system config array field renderer
 */
class Cashback extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_country;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
        
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('cashback', ['label' => __('Product Sku'), 'width' => _('1000')]);
        $this->addColumn('mode', ['label' => __('Mode')]);
        $this->addColumn('amount', ['label' => __('Amount'), 'class'=>'not-negative-amount']);
        $this->addColumn('max', ['label' => __('Max Amount'), 'class'=>'not-negative-amount']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
     public function renderCellTemplate($columnName)
    {
        if ($columnName == 'mode') {
         
            $options = [['value'=>'fixed','label'=>"Fixed"],['value'=>'percentage','label'=>"Percentage"]];
         	$element = $this->_elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            );
            return str_replace("\n", '', $element->getElementHtml());
        }
        
        

        return parent::renderCellTemplate($columnName);
    } 
}
