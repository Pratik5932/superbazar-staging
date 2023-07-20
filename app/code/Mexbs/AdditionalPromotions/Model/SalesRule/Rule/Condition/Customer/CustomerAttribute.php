<?php
namespace Mexbs\AdditionalPromotions\Model\SalesRule\Rule\Condition\Customer;

class CustomerAttribute extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * All attribute values as array in form:
     * array(
     *   [entity_id_1] => array(
     *          [store_id_1] => store_value_1,
     *          [store_id_2] => store_value_2,
     *          ...
     *          [store_id_n] => store_value_n
     *   ),
     *   ...
     * )
     *
     * Will be set only for not global scope attribute
     *
     * @var array
     */
    protected $_entityAttributeValues = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    protected $customerSession;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $_attrSetCollection;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;


    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Eav\Model\Config $config,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = []
    ) {
        $this->_config = $config;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerResource = $customerResource;
        $this->customerSession = $customerSession;
        $this->_attrSetCollection = $attrSetCollection;
        $this->_localeFormat = $localeFormat;
        parent::__construct($context, $data);
    }


    /**
     * Retrieve attribute object
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttributeObject()
    {
        try {
            $obj = $this->_config->getAttribute(\Magento\Customer\Model\Customer::ENTITY, $this->getAttribute());
        } catch (\Exception $e) {
            $obj = new \Magento\Framework\DataObject();
            $obj->setEntity($this->customerFactory->create())->setFrontendInput('text');
        }
        return $obj;
    }

    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['entity_id'] = __('ID');
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = $this->customerResource->loadAllAttributes()->getAttributesByCode();

        $attributes = [];
        foreach ($customerAttributes as $attribute) {
            /* @var $attribute \Magento\Customer\Model\ResourceModel\Attribute */
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = $this->_config->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
            $selectOptions = $this->_attrSetCollection
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } elseif ($this->getAttribute() === 'type_id') {
            foreach ($selectReady as $value => $label) {
                if (is_array($label) && isset($label['value'])) {
                    $selectOptions[] = $label;
                } else {
                    $selectOptions[] = ['value' => $value, 'label' => $label];
                }
            }
            $selectReady = null;
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    /**
     * Set new values only if we really got them
     *
     * @param array $selectOptions
     * @param array $selectReady
     * @param array $hashedReady
     * @return $this
     */
    protected function _setSelectOptions($selectOptions, $selectReady, $hashedReady)
    {
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $option) {
                    if (is_array($option['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$option['value']] = $option['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }
        return $this;
    }

    /**
     * Retrieve value by option
     *
     * @param string|null $option
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();
        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData('value_select_options');
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        return '';
    }

    /**
     * Retrieve attribute element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }


    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }


    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        $isContainsOperator = !empty($arr['operator']) && in_array($arr['operator'], ['{}', '!{}']);
        if ($attribute && $attribute->getBackendType() == 'decimal' && !$isContainsOperator) {
            if (isset($arr['value'])) {
                if (!empty($arr['operator']) && in_array(
                    $arr['operator'],
                    ['!()', '()']
                ) && false !== strpos(
                    $arr['value'],
                    ','
                )
                ) {
                    $tmp = [];
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = $this->_localeFormat->getNumber($value);
                    }
                    $arr['value'] = implode(',', $tmp);
                } else {
                    $arr['value'] = $this->_localeFormat->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset(
                $arr['is_value_parsed']
            ) ? $this->_localeFormat->getNumber(
                $arr['is_value_parsed']
            ) : false;
        }

        return parent::loadArray($arr);
    }

    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $addressCustomerId = $model->getCustomerId();
        if(!$addressCustomerId){
            return false;
        }

        $loggedInCustomerId = $this->customerSession->getCustomerId();
        if(!$loggedInCustomerId
            || ($addressCustomerId != $loggedInCustomerId)){
            return false;
        }

        $customer = $this->customerFactory->create()->load($addressCustomerId);

        $attrCode = $this->getAttribute();

        if($attrCode == 'dob'){
            $valueParsedTimeStamp = strtotime($this->getValueParsed());
            $valueParsedStripped = date('Y-m-d', $valueParsedTimeStamp);
           $this->setValueParsed($valueParsedStripped);
        }

        if (!isset($this->_entityAttributeValues[$customer->getId()])) {
            if (!$customer->getResource()) {
                return false;
            }
            $attr = $customer->getResource()->getAttribute($attrCode);

            if ($attr && $attr->getBackendType() == 'datetime' && !is_int($this->getValue())) {
                $this->setValue(strtotime($this->getValue()));
                $value = strtotime($customer->getData($attrCode));
                return $this->validateAttribute($value);
            }

            if ($attr && $attr->getFrontendInput() == 'multiselect') {
                $value = $customer->getData($attrCode);
                $value = strlen($value) ? explode(',', $value) : [];
                return $this->validateAttribute($value);
            }

            return parent::validate($customer);
        } else {
            $result = false;
            // any valid value will set it to TRUE
            // remember old attribute state
            $oldAttrValue = $customer->hasData($attrCode) ? $customer->getData($attrCode) : null;

            foreach ($this->_entityAttributeValues[$customer->getId()] as $value) {
                $attr = $customer->getResource()->getAttribute($attrCode);
                if ($attr && $attr->getBackendType() == 'datetime') {
                    $value = strtotime($value);
                } elseif ($attr && $attr->getFrontendInput() == 'multiselect') {
                    $value = strlen($value) ? explode(',', $value) : [];
                }

                $customer->setData($attrCode, $value);
                $result |= parent::validate($customer);

                if ($result) {
                    break;
                }
            }

            if ($oldAttrValue === null) {
                $customer->unsetData($attrCode);
            } else {
                $customer->setData($attrCode, $oldAttrValue);
            }

            return (bool)$result;
        }
    }


    /**
     * Validate customer by address ID
     * (we expect it never get called)
     *
     * @param int $addressId
     * @return bool
     */
    public function validateByEntityId($addressId)
    {
        return false;
    }
}
