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



namespace Mirasvit\Rewards\Model\Spending\Rule\Condition;

use Mirasvit\Rewards\Model\Earning\Rule\Condition\CustomerFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    private $earningRuleConditionCustomerFactory;

    protected $spendingRuleConditionProductFactory;

    protected $ruleConditionAddressFactory;

    protected $spendingRuleConditionCustomFactory;

    protected $rewardsRule;

    protected $context;

    /**
     * @var \Magento\Rule\Model\Condition\Context
     */
    protected $ruleContext;

    public function __construct(
        CustomerFactory $earningRuleConditionCustomerFactory,
        AddressFactory $ruleConditionAddressFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Rule\Model\Condition\Context $ruleContext,
        array $data = []
    ) {
        parent::__construct($ruleContext, $data);

        $this->earningRuleConditionCustomerFactory = $earningRuleConditionCustomerFactory;
        $this->ruleConditionAddressFactory         = $ruleConditionAddressFactory;
        $this->ruleContext                         = $ruleContext;
        $this->context                             = $context;

        $this->setType('\\Mirasvit\\Rewards\\Model\\Spending\\Rule\\Condition\\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $type = \Mirasvit\Rewards\Model\Spending\Rule::TYPE_CART;

        if ($type == \Mirasvit\Rewards\Model\Spending\Rule::TYPE_CUSTOM) {
            $itemAttributes = $this->_getCustomAttributes();
            $condition      = 'custom';
        } elseif ($type == \Mirasvit\Rewards\Model\Spending\Rule::TYPE_CART) {
            return $this->_getCartConditions();
        } else {
            $itemAttributes = $this->_getProductAttributes();
            $condition      = 'product';
        }

        $attributes = [];
        foreach ($itemAttributes as $code => $label) {
            $group                = $this->rewardsRule->getAttributeGroup($code);
            $attributes[$group][] = [
                'value' => '\\Mirasvit\\Rewards\\Model\\Spending\\Rule\\Condition\\' . $condition . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            [
                'value' => '\\Mirasvit\\Rewards\\Model\\Spending\\Rule\\Condition\\Combine',
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
    private function convertCustomerAttributes($itemAttributes, $condition, $group)
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection $productCollection
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
        $productCondition  = $this->spendingRuleConditionProductFactory->create();
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();

        return $productAttributes;
    }

    /**
     * @return array
     */
    protected function _getCartConditions()
    {
        $addressCondition  = $this->ruleConditionAddressFactory->create();
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributes        = [];

        foreach ($addressAttributes as $code => $label) {
            $attributes[] = ['value' => 'Mirasvit\Rewards\Model\Spending\Rule\Condition\Address|' . $code, 'label' => $label];
        }

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
            ['value' => 'Magento\SalesRule\Model\Rule\Condition\Combine', 'label' => __('Conditions combination')],
            ['label' => __('Cart Attribute'), 'value' => $attributes],
        ]);

        $additional = new \Magento\Framework\DataObject();
        $this->context->getEventManager()->dispatch('salesrule_rule_condition_combine', ['additional' => $additional]);

        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        $itemAttributes     = $this->_getCustomerAttributes();
        $customerAttributes = $this->convertCustomerAttributes($itemAttributes, 'customer', 'Customer');
        $conditions         = array_merge_recursive($conditions, [
            [
                'label' => __('Customer'),
                'value' => $customerAttributes['Customer'],
            ],
        ]);

        return $conditions;
    }

    /**
     * @return array
     */
    protected function _getCustomAttributes()
    {
        $customCondition  = $this->spendingRuleConditionCustomFactory->create();
        $customAttributes = $customCondition->loadAttributeOptions()->getAttributeOption();

        return $customAttributes;
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
     * @inheritDoc
     */
    public function asHtmlRecursive()
    {
        $this->setJsFormObject($this->getFormName() . 'rule_actions_fieldset');

        return parent::asHtmlRecursive();
    }
}
