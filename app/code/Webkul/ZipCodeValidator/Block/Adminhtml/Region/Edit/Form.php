<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Block\Adminhtml\Region\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('region_form');
        $this->setTitle(__('Region Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('zipcodevalidator');
        $form = $this->_formFactory->create(
            ['data' => [
                        'id' => 'edit_form',
                        'enctype' => 'multipart/form-data',
                        'action' => $this->getData('action'),
                        'method' => 'post']
                    ]
        );
        $form->setHtmlIdPrefix('region_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Region Information'), 'class' => 'fieldset-wide']
        );
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'region_id']);
        }
        $fieldset->addField(
            'region_name',
            'text',
            ['name' => 'region_name', 'label' => __('Region Name'), 'title' => __('Region Name'), 'required' => true, 'class' => 'validate-no-html-tags']
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        $fieldset->addField(
            'zipcodes-csv',
            'file',
            ['name' => 'zipcodes-csv', 'label' => __('CSV'), 'title' => __('CSV')]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
