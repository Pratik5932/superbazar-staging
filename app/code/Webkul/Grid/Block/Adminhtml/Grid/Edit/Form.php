<?php
/**
* Webkul_Grid Add New Row Form Admin Block.
* @category    Webkul
* @package     Webkul_Grid
* @author      Webkul Software Private Limited
*
*/
namespace Webkul\Grid\Block\Adminhtml\Grid\Edit;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;
/**
* Adminhtml Add New Row Form.
*/
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
    * @var \Magento\Store\Model\System\Store
    */
        protected $_systemStore;
        protected $_appConfigScopeConfigInterface;
    /**
    * @var Config
    */
    protected $_paymentModelConfig;


    /**
    * @param \Magento\Backend\Block\Template\Context $context,
    * @param \Magento\Framework\Registry $registry,
    * @param \Magento\Framework\Data\FormFactory $formFactory,
    * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
    * @param \Webkul\Grid\Model\Status $options,
    */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Webkul\Grid\Model\Status $options,
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig,
        array $data = []
    ) {
        $this->_options = $options;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
    * Prepare form.
    *
    * @return $this
    */
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('wkgrid_');
        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Item'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Item'), 'class' => 'fieldset-wide']
            );
        }

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_appConfigScopeConfigInterface
            ->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode
            );
        }
        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);
      
        $fieldset->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Status'),
                'id' => 'is_active',
                'title' => __('Status'),
                'values' => $this->_options->getOptionArray(),
                'class' => 'status',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'select_method',
            'select',
            [
                'name' => 'select_method',
                'label' => __('Select Payment Method'),
                'id' => 'select_method',
                'title' => __('Address Type'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $methods
            ]
        );
        $afterElementHtml = '<p class="nm"><small>' . 'Add coma separated postcode which you want to hide payment method! ' . '</small></p>';

        $fieldset->addField(
            'postcode',
            'textarea',
            [
                'name' => 'postcode',
                'label' => __('Postcodes'),
                'id' => 'postcode',
                'title' => __('Postcode'),
                'required' => true,
                // 'style' => 'display:none'
                'after_element_html' => $afterElementHtml,
            ]
        );



        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
