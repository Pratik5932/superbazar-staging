<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_MpHyperLocal
* @author    Webkul
* @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/
namespace Webkul\MpHyperLocal\Block\Adminhtml\Ship\Area\Edit\Tab;

/**
* @SuppressWarnings(PHPMD.DepthOfInheritance)
*/
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
    * Prepare form fields
    *
    * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
    * @return \Magento\Backend\Block\Widget\Form
    */
    protected function _prepareForm()
    {
        /** @var $model \Webkul\MpHyperLocal\Model\ShipArea */
        $model = $this->_coreRegistry->registry('ship_area');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Ship Area Information')]);
        $data = [];
        if ($model->getEntityId()) {
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
            $data = [
                'entity_id' => $model->getEntityId(),
                'autocomplete' => $model->getAddress(),
                'mphyperlocal_general_settings_latitude' => $model->getLatitude(),
                'mphyperlocal_general_settings_longitude' => $model->getLongitude(),
                'address_type' => $model->getAddressType(),
                'postcode' => $model->getPostcode(),
                'abn' => $model->getAbn(),
                'prefix' => $model->getPrefix(),
                'free_delivery' => $model->getFreeDelivery(),
                'delivery_days' => $model->getDeliveryDays(),
            ];
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        if ($this->getFilter() != 'zipcode') {
            $select = [
                '' => __('--select--'),
                'city' => __('City'), 
                'state' => __('State') , 
                'country' => __('Country')
            ];
        } else {
            $select = [
                '' => __('--select--'),
                'postcode'=> __('Postcode')
            ];
        }

        $baseFieldset->addField(
            'address_type',
            'select',
            [
                'name' => 'address_type',
                'label' => __('Address Type'),
                'id' => 'address_type',
                'title' => __('Address Type'),
                'class' => 'required-entry',
                'required' => true,
                'options' => $select
            ]
        );

        if ($this->getFilter() != 'zipcode') {
            $baseFieldset->addField(
                'autocomplete',
                'text',
                [
                    'name' => 'address',
                    'label' => __('Address'),
                    'id' => 'autocomplete',
                    'title' => __('Ship Area Address'),
                    'required' => true,
                    'class' => 'required-entry location'
                ]
            );
        }

        if ($this->getFilter() == 'zipcode') {
            $baseFieldset->addField(
                'postcode',
                'text',
                [
                    'name' => 'postcode',
                    'label' => __('Postcode'),
                    'id' => 'postcode',
                    'title' => __('Postcode'),
                    'required' => true,
                    // 'style' => 'display:none'
                ]
            );
        }
        if ($this->getFilter() == 'zipcode') {
            $baseFieldset->addField(
                'abn',
                'text',
                [
                    'name' => 'abn',
                    'label' => __('ABN Number'),
                    'id' => 'abn',
                    'title' => __('ABN Number'),
                    'required' => false,
                    // 'style' => 'display:none'
                ]
            );
        }
        if ($this->getFilter() == 'zipcode') {
            $baseFieldset->addField(
                'free_delivery',
                'text',
                [
                    'name' => 'free_delivery',
                    'label' => __('Free Delivery'),
                    'id' => 'free_delivery',
                    'title' => __('Free Delivery'),
                    'required' => false,
                    // 'style' => 'display:none'
                ]
            );
        }
        if ($this->getFilter() == 'zipcode') {
            $baseFieldset->addField(
                'delivery_days',
                'text',
                [
                    'name' => 'delivery_days',
                    'label' => __('Delivery days'),
                    'id' => 'delivery_days',
                    'title' => __('Delivery days'),
                    'required' => false,
                    // 'style' => 'display:none'
                ]
            );
        }

        if ($this->getFilter() == 'zipcode') {
            $baseFieldset->addField(
                'prefix',
                'text',
                [
                    'name' => 'prefix',
                    'label' => __('Order Number prefix'),
                    'id' => 'abn',
                    'title' => __('Order Number prefix'),
                    'required' => false,
                    // 'style' => 'display:none'
                ]
            );
        }

        if ($this->getFilter() != 'zipcode') {
            $baseFieldset->addField(
                'mphyperlocal_general_settings_latitude',
                'text',
                [
                    'name' => 'latitude',
                    'label' => __('Latitude'),
                    'id' => 'latitude',
                    'title' => __('Amazon Access Key Id'),
                    'class' => 'required-entry location',
                    'required' => true
                ]
            );
        }

        if ($this->getFilter() != 'zipcode') {
            $baseFieldset->addField(
                'mphyperlocal_general_settings_longitude',
                'text',
                [
                    'name' => 'longitude',
                    'label' => __('Longitude'),
                    'id' => 'longitude',
                    'title' => __('Longitude'),
                    'class' => 'required-entry location',
                    'required' => true
                ]
            );
        }

        if ($this->getFilter() != 'zipcode') {
            $Lastfield = $form->getElement('mphyperlocal_general_settings_longitude');
            $Lastfield->setAfterElementHtml(
                '<script type="text/x-magento-init">
                {
                "body": {
                "autofilladdress": {
                "googleApiKey":"'.$this->getGoogleApiKey().'",
                "savedAddress":"'. $this->getSavedAddress($data).'",
                "filter":"'.$this->getFilter().'"
                }
                }
                }
                </script>'
            );
        }

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
    * Return Google Api Key.
    *
    * @return string
    */

    public function getGoogleApiKey()
    {
        return $this->_scopeConfig->getValue('mphyperlocal/general_settings/google_api_key');
    }

    /**
    * getSavedAddress.
    * @param array
    * @return string
    */
    public function getSavedAddress($data)
    {
        return isset($data['autocomplete']) ? $data['autocomplete'] : '';
    }

    protected function getFilter() {
        return $this->_scopeConfig->getValue('mphyperlocal/general_settings/show_collection');
    }
}
