<?php
namespace MageArray\StorePickup\Block\Adminhtml\Store\Edit\Tab;

/**
 * Class Main
 * @package MageArray\StorePickup\Block\Adminhtml\Store\Edit\Tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{


    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Config\Model\Config\Source\Locale\Weekdays $weekDays,
        \Magento\Config\Model\Config\Source\Locale\Country $countries,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_weekDays = $weekDays;
        $this->_countries = $countries;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('store_post');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Store Information')]
        );
        if ($model->getId()) {
            $fieldset->addField('storepickup_id', 'hidden',
                ['name' => 'storepickup_id']);
        }


        $fieldset->addField(
            'store_name',
            'text',
            [
                'label' => __('Store Name'),
                'title' => __('Store Name'),
                'name' => 'store_name',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'phone_number',
            'text',
            [
                'name' => 'phone_number',
                'label' => __('Phone Number'),
                'title' => __('Phone Number'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'opening_days',
            'multiselect',
            [
                'label' => __('Working Days'),
                'title' => __('Working Days'),
                'name' => 'opening_days',
                'values' => $this->_weekDays->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'working_hours',
            'text',
            [
                'label' => __('Working Hours'),
                'title' => __('Working Hours'),
                'name' => 'working_hours',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'name' => 'sort_order',
            ]
        );

        $fieldsetSecond = $form->addFieldset(
            'base_fieldset_second',
            ['legend' => __('Store Address')]
        );

        $fieldsetSecond->addField(
            'address',
            'text',
            [
                'label' => __('Address'),
                'title' => __('Address'),
                'name' => 'address',
                'required' => true,
            ]
        );

        $fieldsetSecond->addField(
            'zipcode',
            'text',
            [
                'label' => __('Zipcode'),
                'title' => __('Zipcode'),
                'name' => 'zipcode',
                'required' => true,
            ]
        );

        $fieldsetSecond->addField(
            'city',
            'text',
            [
                'label' => __('City'),
                'title' => __('City'),
                'name' => 'city',
                'required' => true,
            ]
        );

        $fieldsetSecond->addField(
            'state',
            'text',
            [
                'label' => __('State'),
                'title' => __('State'),
                'name' => 'state',
                'required' => true,
            ]
        );

        $fieldsetSecond->addField(
            'country',
            'select',
            [
                'label' => __('Country'),
                'title' => __('Country'),
                'name' => 'country',
                'values' => $this->_countries->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldsetSecond->addField(
            'latitude',
            'text',
            [
                'label' => __('Latitude'),
                'title' => __('Latitude'),
                'name' => 'latitude',
            ]
        );

        $fieldsetSecond->addField(
            'longitude',
            'text',
            [
                'label' => __('Longitude'),
                'title' => __('Longitude'),
                'name' => 'longitude',
            ]
        );


        $googleMap = $this->getLayout()
            ->createBlock('MageArray\StorePickup\Block\Adminhtml\Store\Edit\Tab\Map');

        $fieldsetSecond->addField(
            'map',
            'text',
            [
                'label' => __('Store Map'),
                'name' => 'map',
            ]
        )->setRenderer($googleMap);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getTabLabel()
    {
        return __('Store Information');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('Store Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @param $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
