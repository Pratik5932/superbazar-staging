<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rewards\Model\Earning\Rule\Condition;

use Magento\ConfigurableProduct\Model\LinkManagement;
use Mirasvit\Rewards\Model\Config as Config;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    private $eventManager;

    private $earningRuleConditionCartFactory;

    private $earningRuleConditionCustomerFactory;

    private $earningRuleConditionProductFactory;

    private $earningRuleConditionReferredCustomerFactory;

    private $earningRuleConditionRmaFactory;

    private $earningRuleConditionOrderFactory;

    private $linkManagement;

    private $moduleManager;

    private $request;

    private $ruleConditionAddressFactory;

    public function __construct(
        ProductFactory $earningRuleConditionProductFactory,
        CustomerFactory $earningRuleConditionCustomerFactory,
        CartFactory $earningRuleConditionCartFactory,
        Referred\CustomerFactory $earningRuleConditionReferredCustomerFactory,
        RmaFactory $earningRuleConditionRmaFactory,
        OrderFactory $earningRuleConditionOrderFactory,
        LinkManagement $linkManagement,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\SalesRule\Model\Rule\Condition\AddressFactory $ruleConditionAddressFactory,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->earningRuleConditionProductFactory          = $earningRuleConditionProductFactory;
        $this->earningRuleConditionCustomerFactory         = $earningRuleConditionCustomerFactory;
        $this->earningRuleConditionCartFactory             = $earningRuleConditionCartFactory;
        $this->earningRuleConditionReferredCustomerFactory = $earningRuleConditionReferredCustomerFactory;
        $this->earningRuleConditionOrderFactory            = $earningRuleConditionOrderFactory;
        $this->earningRuleConditionRmaFactory              = $earningRuleConditionRmaFactory;
        $this->linkManagement                              = $linkManagement;
        $this->request                                     = $request;
        $this->moduleManager                               = $moduleManager;
        $this->ruleConditionAddressFactory                 = $ruleConditionAddressFactory;
        $this->eventManager                                = $eventManager;

        $this->setType('\\Mirasvit\\Rewards\\Model\\Earning\\Rule\\Condition\\Combine');
    }

    /**
     * @return array
     */
    private function getReferralEvents()
    {
        return [
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_ORDER,
            Config::BEHAVIOR_TRIGGER_REFERRED_CUSTOMER_SIGNUP,
        ];
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        if ($this->getRule()->getType()) {
            $type = $this->getRule()->getType();
        } else {
            $type = $this->request->getParam('rule_type');
        }

        if ($type == \Mirasvit\Rewards\Model\Earning\Rule::TYPE_CART) {
            return $this->_getCartConditions();
        } elseif ($type == \Mirasvit\Rewards\Model\Earning\Rule::TYPE_BEHAVIOR) {
            $itemAttributes = $this->_getCustomerAttributes();
            $attributes     = $this->convertToAttributes($itemAttributes, 'customer', 'Customer');

            if (in_array($this->getRule()->getBehaviorTrigger(), $this->getReferralEvents())) {
                $itemAttributes     = $this->_getReferredCustomerAttributes();
                $attributesReffered = $this->convertToAttributes(
                    $itemAttributes,
                    'Referred\Customer',
                    'Referred Customer'
                );
                $attributes         = array_merge_recursive($attributes, $attributesReffered);
            }

            if ($this->moduleManager->isEnabled('Mirasvit_Rma')) {
                $itemAttributes     = $this->_getRmaAttributes();
                $attributesReffered = $this->convertToAttributes(
                    $itemAttributes,
                    'Rma',
                    'RMA'
                );
                $attributes         = array_merge_recursive($attributes, $attributesReffered);
            }

            $orderAttributes = $this->getOrderAttributes();
            $attributes      = array_merge_recursive($attributes, $orderAttributes);
        } else {
            $itemAttributes = $this->_getProductAttributes();
            $attributes     = $this->convertToAttributes($itemAttributes, 'product', 'Product Attributes');

            $itemAttributes     = $this->_getCustomerAttributes();
            $customerAttributes = $this->convertToAttributes($itemAttributes, 'customer', 'Customer');
            $attributes         = array_merge_recursive($attributes, $customerAttributes);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => '\\Mirasvit\\Rewards\\Model\\Earning\\Rule\\Condition\\Combine',
                'label' => __('Conditions Combination'),
            ],
        ]);

        foreach ($attributes as $group => $arrAttributes) {
            $conditions = array_merge_recursive($conditions, [
                [
                    'label' => $group,
                    'value' => $arrAttributes,
                ],
            ]);
        }

        return $conditions;
    }

    /**
     * @param array  $itemAttributes
     * @param string $condition
     * @param string $group
     *
     * @return array
     */
    protected function convertToAttributes($itemAttributes, $condition, $group)
    {
        $attributes = [];
        foreach ($itemAttributes as $code => $label) {
            $attributes[$group][] = [
                'value' => '\\Mirasvit\\Rewards\\Model\\Earning\\Rule\\Condition\\' . ucfirst($condition) . '|' . $code,
                'label' => $label,
            ];
        }

        return $attributes;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     *
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function _getProductAttributes()
    {
        $productCondition  = $this->earningRuleConditionProductFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();

        return $productAttributes;
    }

    /**
     * @return array
     */
    protected function _getCartConditions()
    {
        $attributes = [];

        $addressCondition  = $this->ruleConditionAddressFactory->create();
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();

        if (!isset($addressAttributes['payment_method'])) {
            $attributes[] = [
                'value' => 'Mirasvit\Rewards\Model\Earning\Rule\Condition\Address|payment_method',
                'label' => __('Payment Method'),
            ];
        }

        if (!isset($addressAttributes['payment_method_additional'])) {
            $attributes[] = [
                'value' => 'Mirasvit\Rewards\Model\Earning\Rule\Condition\Address|payment_method_additional',
                'label' => __('Additional Payment Method'),
            ];
        }

        foreach ($addressAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Mirasvit\Rewards\Model\Earning\Rule\Condition\Address|' . $code,
                'label' => $label,
            ];
        }

        $cartCondition  = $this->earningRuleConditionCartFactory->create();
        $cartAttributes = $cartCondition->loadAttributeOptions()->getAttributeOption();

        foreach ($cartAttributes as $code => $label) {
            $attributes[] = ['value' => 'Mirasvit\Rewards\Model\Earning\Rule\Condition\Cart|' . $code, 'label' => $label];
        }

        $itemAttributes     = $this->_getCustomerAttributes();
        $customerAttributes = $this->convertToAttributes($itemAttributes, 'customer', 'Customer');

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Found',
                'label' => __('Product attribute combination'),
            ],
            [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Subselect',
                'label' => __('Products subselection'),
            ],
            [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
                'label' => __('Conditions combination'),
            ],
            [
                'label' => __('Cart Attribute'),
                'value' => $attributes,
            ],
            [
                'label' => __('Customer'),
                'value' => $customerAttributes['Customer'],
            ],
        ]);

        $additional = new \Magento\Framework\DataObject();
        $this->eventManager->dispatch('salesrule_rule_condition_combine', ['additional' => $additional]);

        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }

    /**
     * @return array
     */
    protected function _getCustomerAttributes()
    {
        $customerCondition  = $this->earningRuleConditionCustomerFactory->create();
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();

        return $customerAttributes;
    }

    /**
     * @return array
     */
    protected function _getReferredCustomerAttributes()
    {
        $customerCondition  = $this->earningRuleConditionReferredCustomerFactory->create();
        $customerAttributes = $customerCondition->loadAttributeOptions()->getAttributeOption();

        return $customerAttributes;
    }

    /**
     * @return array
     */
    protected function _getRmaAttributes()
    {
        $rmaCondition  = $this->earningRuleConditionRmaFactory->create();
        $rmaAttributes = $rmaCondition->loadAttributeOptions()->getAttributeOption();

        return $rmaAttributes;
    }

    /**
     * @return array
     */
    protected function getOrderAttributes()
    {
        $orderCondition  = $this->earningRuleConditionOrderFactory->create();
        $orderAttributes = $orderCondition->loadAttributeOptions()->getAttributeOption();

        return $this->convertToAttributes(
            $orderAttributes,
            'Order',
            'Order'
        );;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        if ($model->getIsProductPage()) { // convert to address
            $this->skipCartConditions($this);

            $model->setPriceCalculation(false);
            $model->setProduct($model);
            $model->setAllItems([$model]);
        }
        \Magento\Framework\Profiler::stop(__METHOD__);

        \Magento\Framework\Profiler::start(__METHOD__ . ' PARENT');
        $isValid = parent::validate($model);
        \Magento\Framework\Profiler::stop(__METHOD__ . ' PARENT');

        return $isValid;
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $rule
     *
     * @return void
     */
    private function skipCartConditions($rule)
    {
        \Magento\Framework\Profiler::start(__METHOD__);

        $conditions = $rule->getConditions();
        foreach ($conditions as $k => $condition) {
            if ($condition instanceof \Magento\SalesRule\Model\Rule\Condition\Address ||
                $condition instanceof \Magento\SalesRule\Model\Rule\Condition\Product\Subselect ||
                $condition instanceof \Mirasvit\Rewards\Model\Earning\Rule\Condition\Cart ||
                strpos((string) $condition->getAttribute(), 'quote') !== false ||
                strpos((string) get_class($condition), 'Amasty') !== false // ignore Amasty conditions
            ) {
                unset($conditions[$k]);
                continue;
            }

            if ($condition->getConditions() && count($condition->getConditions())) {
                $this->skipCartConditions($condition);
            }
        }

        if ($conditions) {
            $rule->setConditions($conditions);
        } else {
            $rule->setConditions([]);
        }

        \Magento\Framework\Profiler::stop(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function asHtmlRecursive()
    {
        $this->setJsFormObject($this->getFormName() . 'rule_conditions_fieldset');

        return parent::asHtmlRecursive();
    }
}
